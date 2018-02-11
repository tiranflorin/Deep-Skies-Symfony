<?php

namespace Dso\ObservationsLogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\View\TwitterBootstrapView;

use Dso\ObservationsLogBundle\Entity\ObsList;
use Dso\ObservationsLogBundle\Form\ObsListType;
use Dso\ObservationsLogBundle\Form\ObsListFilterType;

/**
 * ObsList controller.
 *
 * @Route("/obs-lists")
 */
class ObsListController extends Controller
{
    /**
     * Lists all ObsList entities.
     *
     * @Route("/", name="obs-lists")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        list($filterForm, $queryBuilder) = $this->filter();

        list($entities, $pagerHtml) = $this->paginator($queryBuilder);

        return array(
            'entities' => $entities,
            'pagerHtml' => $pagerHtml,
            'filterForm' => $filterForm->createView(),
        );
    }

    /**
    * Create filter form and process filter request.
    *
    */
    protected function filter()
    {
        $request = $this->getRequest();
        $session = $request->getSession();
        $filterForm = $this->createForm(new ObsListFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('DsoObservationsLogBundle:ObsList')->createQueryBuilder('e');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('ObsListControllerFilter');
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
                $session->set('ObsListControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('ObsListControllerFilter')) {
                $filterData = $session->get('ObsListControllerFilter');
                $filterForm = $this->createForm(new ObsListFilterType(), $filterData);
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
            return $me->generateUrl('obs-lists', array('page' => $page));
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
     * Creates a new ObsList entity.
     *
     * @Route("/", name="obs-lists_create")
     * @Method("POST")
     * @Template("DsoObservationsLogBundle:ObsList:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new ObsList();
        $form = $this->createForm(new ObsListType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('obs-lists_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new ObsList entity.
     *
     * @Route("/new", name="obs-lists_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new ObsList();
        $form   = $this->createForm(new ObsListType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a ObsList entity.
     *
     * @Route("/{id}", name="obs-lists_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DsoObservationsLogBundle:ObsList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ObsList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing ObsList entity.
     *
     * @Route("/{id}/edit", name="obs-lists_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DsoObservationsLogBundle:ObsList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ObsList entity.');
        }

        $editForm = $this->createForm(new ObsListType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing ObsList entity.
     *
     * @Route("/{id}", name="obs-lists_update")
     * @Method("PUT")
     * @Template("DsoObservationsLogBundle:ObsList:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DsoObservationsLogBundle:ObsList')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ObsList entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new ObsListType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('obs-lists_edit', array('id' => $id)));
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
     * Deletes a ObsList entity.
     *
     * @Route("/{id}", name="obs-lists_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DsoObservationsLogBundle:ObsList')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ObsList entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('obs-lists'));
    }

    /**
     * Creates a form to delete a ObsList entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
