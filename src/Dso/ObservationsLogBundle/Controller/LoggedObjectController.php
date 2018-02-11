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

use Dso\ObservationsLogBundle\Entity\LoggedObject;
use Dso\ObservationsLogBundle\Form\LoggedObjectType;
use Dso\ObservationsLogBundle\Form\LoggedObjectFilterType;

/**
 * LoggedObject controller.
 *
 * @Route("/logged-objects")
 */
class LoggedObjectController extends Controller
{
    /**
     * Lists all LoggedObject entities.
     *
     * @Route("/", name="logged-objects")
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
        $filterForm = $this->createForm(new LoggedObjectFilterType());
        $em = $this->getDoctrine()->getManager();
        $queryBuilder = $em->getRepository('DsoObservationsLogBundle:LoggedObject')->createQueryBuilder('e');

        // Reset filter
        if ($request->get('filter_action') == 'reset') {
            $session->remove('LoggedObjectControllerFilter');
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
                $session->set('LoggedObjectControllerFilter', $filterData);
            }
        } else {
            // Get filter from session
            if ($session->has('LoggedObjectControllerFilter')) {
                $filterData = $session->get('LoggedObjectControllerFilter');
                $filterForm = $this->createForm(new LoggedObjectFilterType(), $filterData);
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
            return $me->generateUrl('logged-objects', array('page' => $page));
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
     * Creates a new LoggedObject entity.
     *
     * @Route("/", name="logged-objects_create")
     * @Method("POST")
     * @Template("DsoObservationsLogBundle:LoggedObject:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity  = new LoggedObject();
        $form = $this->createForm(new LoggedObjectType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.create.success');

            return $this->redirect($this->generateUrl('logged-objects_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new LoggedObject entity.
     *
     * @Route("/new", name="logged-objects_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new LoggedObject();
        $form   = $this->createForm(new LoggedObjectType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a LoggedObject entity.
     *
     * @Route("/{id}", name="logged-objects_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DsoObservationsLogBundle:LoggedObject')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find LoggedObject entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing LoggedObject entity.
     *
     * @Route("/{id}/edit", name="logged-objects_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DsoObservationsLogBundle:LoggedObject')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find LoggedObject entity.');
        }

        $editForm = $this->createForm(new LoggedObjectType(), $entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing LoggedObject entity.
     *
     * @Route("/{id}", name="logged-objects_update")
     * @Method("PUT")
     * @Template("DsoObservationsLogBundle:LoggedObject:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('DsoObservationsLogBundle:LoggedObject')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find LoggedObject entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new LoggedObjectType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.update.success');

            return $this->redirect($this->generateUrl('logged-objects_edit', array('id' => $id)));
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
     * Deletes a LoggedObject entity.
     *
     * @Route("/{id}", name="logged-objects_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('DsoObservationsLogBundle:LoggedObject')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find LoggedObject entity.');
            }

            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('success', 'flash.delete.success');
        } else {
            $this->get('session')->getFlashBag()->add('error', 'flash.delete.error');
        }

        return $this->redirect($this->generateUrl('logged-objects'));
    }

    /**
     * Creates a form to delete a LoggedObject entity by id.
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
