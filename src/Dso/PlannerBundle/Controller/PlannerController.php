<?php

namespace Dso\PlannerBundle\Controller;

use Doctrine\ORM\EntityManager;
use Dso\PlannerBundle\Exception\HijackException;
use Dso\PlannerBundle\Form\Type\CustomFilters;
use Dso\PlannerBundle\Services\CreateVisibleObjectsTable;
use Dso\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Dso\PlannerBundle\Services\FilterResults;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;

class PlannerController extends Controller
{
    public function indexAction()
    {
        return $this->render('DsoPlannerBundle:Planner:index.html.twig', array(
            'formCustomFilters' => $this->createForm(new CustomFilters())->createView()
        ));

    }

    public function filterPredefinedAction(Request $request)
    {
        /** @var FilterResults $filterService */
        $filterService = $this->get('dso_planner.filter_results');
        $filterType = $request->get('filter_type');

        if($filterType != 'predefined') {
            throw new HijackException('Hijack attempt. Bye!', Response::HTTP_CONFLICT);
        }

        $selection = $filterService->getSelectedFilter($request->get('selection'));
        $user = $this->get('security.context')->getToken()->getUser();

        $filterService->setConfigurationDetails(
            $this->get('dso_planner.visible_objects')->getVisibleObjectsTableName($user),
            $filterType,
            $selection
        );

        $paginatedResults = $filterService->retrieveFilteredData($request->get('page', 1));
        $paginatedResults->setParam('filter_type', 'predefined');
        $paginatedResults->setParam('selection', $selection);

        return $this->render('DsoPlannerBundle:Planner:index.html.twig', array(
            'formCustomFilters' => $this->createForm(new CustomFilters())->createView(),
            'pagination' => $paginatedResults
        ));
    }

    public function filterCustomAction(Request $request)
    {
        /** @var FilterResults $filterService */
        $filterService = $this->get('dso_planner.filter_results');

        if ($request->getMethod() == 'POST') {
            $form = $this->createForm(new CustomFilters());
            $form->handleRequest($request);

            if ($form->isValid()) {
                $filterType = $form->get('filter_type')->getData();

                if($filterType == 'custom') {
                    $const = $form->get('constellation');
                    $magMin = $form->get('magMin');
                    $magMax = $form->get('magMax');
                    $objType = $form->get('objectType');
                    $selection = array(
                        'constellation' => $const,
                        'magMin' => $magMin,
                        'magMax' => $magMax,
                        'objType' => $objType
                    );
                    $user = $this->get('security.context')->getToken()->getUser();

                    $filterService->setConfigurationDetails(
                        $this->get('dso_planner.visible_objects')->getVisibleObjectsTableName($user),
                        $filterType,
                        $selection
                    );
                }

                $results = $filterService->retrieveFilteredData();
                echo 'ceva';
            }
        }
    }

    /**
     * Displays the edit location settings form
     */
    public function editLocationSettingsAction()
    {
        $user = $this->get('security.context')->getToken()->getUser();
        return $this->render('DsoPlannerBundle:Planner:location_form.html.twig', array(
            'user' => $user
        ));
    }

    /**
     * Save/update location settings for a user
     */
    public function updateLocationSettingsAction(Request $request)
    {
        /** @var SecurityContext $securityContext */
        $securityContext = $this->get('security.context');
        if (false === $securityContext->isGranted(
                'IS_AUTHENTICATED_FULLY'
            )) {
            throw new AccessDeniedException();
        }

        $defaultTime = new \DateTime('now', new \DateTimeZone('UTC'));
        /** @var User $user */
        $user = $securityContext->getToken()->getUser();
        $user->setLatitude($request->request->get('latitude', '43.234'))
            ->setLongitude($request->request->get('longitude', '22.234'))
            ->setTimeZone($request->request->get('timezone', 'Europe/Bucharest'))
            ->setDatetime($request->request->get('datetime', $defaultTime->format('Y-m-dH:i:s')));

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        /** @var  CreateVisibleObjectsTable $visibleObjectsService */
        $visibleObjectsService = $this->get('dso_planner.visible_objects');
        $visibleObjectsService->setConfigurationDetails($user->getUsername(), $user->getLatitude(), $user->getLongitude(), $user->getDateTime());
        try {
            $result = $visibleObjectsService->executeFlow();
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $request->getSession()->getFlashBag()->add(
            'notice',
            'Your new location settings were saved!'
        );

        return $this->redirect($this->generateUrl('fos_user_profile_show'));
    }

    /**
     * Expose current location settings
     */
    public function locationSettingsAction()
    {
        /** @var SecurityContext $securityContext */
        $securityContext = $this->get('security.context');

        if (false === $securityContext->isGranted(
                'IS_AUTHENTICATED_FULLY'
            )) {
            throw new AccessDeniedException();
        }

        $user = $securityContext->getToken()->getUser();
        if (!$user) {
            throw new \LogicException('No user found.');
        }

        return $this->render('DsoPlannerBundle:Planner:location_settings.html.twig', array(
            'user' => $user)
        );
    }
}
