<?php

namespace Dso\PlannerBundle\Controller;

use Dso\PlannerBundle\Exception\HijackException;
use Dso\PlannerBundle\Services\SettingsManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Dso\PlannerBundle\Services\FilterResults;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;

class PlannerController extends Controller
{
    public function indexAction()
    {
        return $this->render('DsoPlannerBundle:Planner:index.html.twig');
    }

    /**
     * Handles the predefined search of visible deep-sky objects with
     * valid options such as "naked_eye", "binoculars" or "small_telescope".
     *
     * @param Request $request
     * @throws \Dso\PlannerBundle\Exception\HijackException
     *
     * @return Response
     */
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
            'pagination' => $paginatedResults
        ));
    }

    /**
     * Handles the custom search of visible deep-sky objects with
     * filters applied for constellation, object type and magnitude.
     *
     * @param Request $request
     * @throws \Dso\PlannerBundle\Exception\HijackException
     *
     * @return Response
     */
    public function filterCustomAction(Request $request)
    {
        /** @var FilterResults $filterService */
        $filterService = $this->get('dso_planner.filter_results');
        $filterType = $request->get('filter_type');

        if($filterType != 'custom') {
            throw new HijackException('Hijack attempt. Bye!', Response::HTTP_CONFLICT);
        }

        $constellations = $request->get('constellation');
        $objTypes = $request->get('obj_type');
        if (empty($constellations) || empty($objTypes)) {
            $request->getSession()->getFlashBag()->add(
                'warning',
                'Invalid search performed! Please select at least one constellation and object type.'
            );

            return $this->redirect($this->generateUrl('dso_planner_homepage'));
        }

        $selection = array(
            'objType' => $objTypes,
            'constellation' => $constellations,
            'magMin' => $request->get('min_mag', 0),
            'magMax' => $request->get('max_mag', 100), // Using 100 as default because of the "99.9" magnitude used in
                                                       // the main 'object' table for objects with undefined magnitude.
        );
        $user = $this->get('security.context')->getToken()->getUser();

        $filterService->setConfigurationDetails(
            $this->get('dso_planner.visible_objects')->getVisibleObjectsTableName($user),
            $filterType,
            $selection
        );

        $paginatedResults = $filterService->retrieveFilteredData($request->get('page', 1));
        $paginatedResults->setParam('filter_type', 'custom');
        $paginatedResults->setParam('selection', $selection);

        return $this->render('DsoPlannerBundle:Planner:index.html.twig', array(
            'pagination' => $paginatedResults
        ));
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
     * Save/update location and time settings for a user
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

        /** @var SettingsManager $settingsManager */
        $settingsManager = $this->get('dso_planner.settings_manager');
        try {
            $settingsManager->updateUserLocation($request, $securityContext->getToken()->getUser());
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
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

    /**
     * An AJAX call is sent after registration with the latitude and longitude of the user.
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function asynchronousUpdateSettingsAction(Request $request)
    {
        if (!$request->isXMLHttpRequest()) {
            return new Response('Hijack attempt!', Response::HTTP_BAD_REQUEST );
        }

        /** @var SettingsManager $settingsManager */
        $settingsManager = $this->get('dso_planner.settings_manager');
        try {
            $settingsManager->updateUserLocation($request, $this->get('security.context')->getToken()->getUser());
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse('ok', Response::HTTP_OK);
    }
}
