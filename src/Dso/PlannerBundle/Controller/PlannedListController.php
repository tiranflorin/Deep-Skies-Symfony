<?php

namespace Dso\PlannerBundle\Controller;

use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;
use Dso\PlannerBundle\Entity\PlannedList;
use Dso\PlannerBundle\Form\Type\PlannedListType;
use Dso\PlannerBundle\Form\Type\PlannedListFilterType;

/**
 * PlannedList controller.
 *
 * @Route("/planner/planned-lists")
 */
class PlannedListController extends Controller
{
    /**
     * Lists all PlannedList entities.
     *
     * @Route("/", name="planner_planned-lists")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();

        list($filterForm, $queryBuilder) = $this->filter();

        $queryBuilder
            ->select('e')
            ->where('e.userId = :userId')
            ->setParameter('userId', $user->getId());

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
        );
    }

    /**
     * Creates a new PlannedList entity.
     *
     * @Route("/", name="planner_planned-lists_create")
     * @Method("POST")
     * @Template("DsoPlannerBundle:PlannedList:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new PlannedList();
        $form = $this->createForm(new PlannedListType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $user = $this->get('security.context')->getToken()->getUser();
            $entity->setUserId($user->getId());

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('planner_planned-lists_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new PlannedList entity.
     *
     * @Route("/new", name="planner_planned-lists_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new PlannedList();
        $form   = $this->createForm(new PlannedListType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a PlannedList entity.
     *
     * @Route("/{id}", name="planner_planned-lists_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DsoPlannerBundle:PlannedList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PlannedList entity.');
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $queryBuilder = $em->createQueryBuilder();
        $query = $queryBuilder
            ->select('d')
            ->distinct()
            ->from('Dso\ObservationsLogBundle\Entity\DeepSkyItem', 'd')
            ->innerJoin(
                'Dso\PlannerBundle\Entity\PlannedObject',
                'p',
                Join::WITH,
                $queryBuilder->expr()->eq('d.id', 'p.objId'))
            ->where('p.userId = :userId')
            ->andWhere('p.listId = :listId')
            ->getQuery();
        $query->setParameter(':userId', $user->getId());
        $query->setParameter(':listId', $entity->getId());
        $plannedObjects = $query->getResult();

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'         => $entity,
            'plannedObjects' => $plannedObjects,
            'delete_form'    => $deleteForm->createView(),
            'remove_item_form'    => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing PlannedList entity.
     *
     * @Route("/{id}/edit", name="planner_planned-lists_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DsoPlannerBundle:PlannedList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PlannedList entity.');
        }

        $editForm = $this->createForm(new PlannedListType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing PlannedList entity.
     *
     * @Route("/{id}", name="planner_planned-lists_update")
     * @Method("PUT")
     * @Template("DsoPlannerBundle:PlannedList:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DsoPlannerBundle:PlannedList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find PlannedList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new PlannedListType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('planner_planned-lists_edit', array('id' => $id)));
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.update.error');
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Deletes a PlannedList entity.
     *
     * @Route("/{id}", name="planner_planned-lists_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DsoPlannerBundle:PlannedList')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find PlannedList entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('planner_planned-lists'));
    }

    /**
     * Removes a DSO from a PlannedList.
     *
     * @Route("/remove-item/{listId}", name="planner_planned-lists_remove_item")
     * @Method("DELETE")
     */
    public function removeFromListAction(Request $request, $listId)
    {
        $form = $this->createDeleteForm($listId);
        $form->bind($request);

        if ($form->isValid()) {
            $dsoId = $request->request->get('dsoId');
            if (empty($dsoId)) {
                $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');

                return $this->redirect($this->generateUrl('planner_planned-lists_show', array('id' => $listId)));
            }

            $em = $this->getDoctrine()->getManager();
            // We can have duplicates.
            $resultsFound = $em->getRepository('DsoPlannerBundle:PlannedObject')->findBy(
                array(
                    'objId' => $dsoId,
                    'listId' => $listId,
                )
            );

            if (!empty($resultsFound)) {
                foreach ($resultsFound as $plannedList) {
                    $em->remove($plannedList);
                }
                $em->flush();
            }

            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('planner_planned-lists_show', array('id' => $listId)));
    }

    /**
     * Create filter form and process filter request.
     *
     */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $filterForm = $this->createForm(new PlannedListFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('DsoPlannerBundle:PlannedList')->createQueryBuilder('e');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('PlannedListControllerFilter');
        }

        // Filter action
        if ($request->get('filter_action') == 'filter') {
            // Bind values from the request
            $filterForm->bind($request);

            if ($filterForm->isValid()) {
                // Build the query from the given form object
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
                // Save filter to session
                $filterData = $filterForm->getData();
                $session->set('PlannedListControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('PlannedListControllerFilter')) {
                $filterData = $session->get('PlannedListControllerFilter');
                $filterForm = $this->createForm(new PlannedListFilterType(), $filterData);
                $this->get('lexik_form_filter.query_builder_updater')->addFilterConditions($filterForm, $queryBuilder);
            }
        }

        return array($filterForm, $queryBuilder);
    }

    /**
     * Get results from paginator and get paginator view.
     *
     */
    protected function paginator($queryBuilder)
    {
        // Paginator
        $adapter = new DoctrineORMAdapter($queryBuilder);
        $pagerfanta = new Pagerfanta($adapter);
        $currentPage = $this->getRequest()->get('page', 1);
        $pagerfanta->setCurrentPage($currentPage);
        $entities = $pagerfanta->getCurrentPageResults();

        // Paginator - route generator
        $me = $this;
        $routeGenerator = function($page) use ($me)
        {
            return $me->generateUrl('planner_planned-lists', array('page' => $page));
        };

        // Paginator - view
        $view = new TwitterBootstrapView();
        $pagerHtml = $view->render($pagerfanta, $routeGenerator, array(
            'proximity' => 3,
            'prev_message' => '« Previous',
            'next_message' => 'Next »',
        ));

        return array($entities, $pagerHtml);
    }

    /**
     * Creates a form to delete a PlannedList entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
