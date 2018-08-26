<?php

namespace Dso\PlannerBundle\Controller;

use Dso\PlannerBundle\Entity\PlannedList;
use Dso\PlannerBundle\Entity\PlannedObject;
use Dso\PlannerBundle\Exception\HijackException;
use Dso\PlannerBundle\Services\SettingsManager;
use Dso\UserBundle\Entity\ObservingSite;
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
        $em = $this->getDoctrine()->getManager();
        $observingSite = $em->find('Dso\UserBundle\Entity\ObservingSite', $user->getCurrentObservingSiteId());

        $filterService->setConfigurationDetails(
            $this->get('dso_planner.visible_objects')->getVisibleObjectsTableName($observingSite, $user->getUsername()),
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

        $em = $this->getDoctrine()->getManager();
        $observingSite = $em->find('Dso\UserBundle\Entity\ObservingSite', $user->getCurrentObservingSiteId());
        $filterService->setConfigurationDetails(
            $this->get('dso_planner.visible_objects')->getVisibleObjectsTableName($observingSite, $user),
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
        $em = $this->getDoctrine()->getManager();
        $observingSite = new ObservingSite();
        if (null !==  $user->getCurrentObservingSiteId()) {
            $observingSite = $em->find('Dso\UserBundle\Entity\ObservingSite', $user->getCurrentObservingSiteId());
        }

        $timezones = array();
        $timestamp = time();
        $zones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        foreach ($zones as $key => $zone) {
            date_default_timezone_set($zone);
            $timezones[$key]['zone'] = $zone;
            $timezones[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }

        return $this->render('DsoPlannerBundle:Planner:location_form.html.twig', array(
            'currentObservingSite' => $observingSite,
            'timezones' => $timezones
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

        $em = $this->getDoctrine()->getManager();
        $observingSite = new ObservingSite();
        if (null !==  $user->getCurrentObservingSiteId()) {
            $observingSite = $em->find('Dso\UserBundle\Entity\ObservingSite', $user->getCurrentObservingSiteId());
        }
        return $this->render('DsoPlannerBundle:Planner:location_settings.html.twig', array(
            'currentObservingSite' => $observingSite)
        );
    }

    /**
     * An AJAX call is sent after registration with the latitude and longitude of the user.
     *
     * @param Request $request
     *
     * @return JsonResponse|Response
     */
    public function asyncUpdateSettingsAction(Request $request)
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

    public function asyncAddItemToListsAction(Request $request)
    {
        if (!$request->isXMLHttpRequest()) {
            return new Response('Hijack attempt!', Response::HTTP_BAD_REQUEST );
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        $listIds = $request->request->get('listIds', array()); // TODO: check if these exist in DB
        $notes = $request->request->get('notes');

        $newObsListName = $request->request->get('listName');
        if (!empty($newObsListName)) {
            $newPlannedItem = $this->createPlannedListItem($newObsListName, $request->request->get('selectedDsoId'));
            array_push($listIds, $newPlannedItem->getId());
        }

        foreach ($listIds as $listId) {
            $plannedObject = new PlannedObject();
            $plannedObject->setObjId($request->request->get('selectedDsoId'))
                ->setListId($listId)
                ->setUserId($user->getId())
                ->setNotes($notes);
            $em->persist($plannedObject);
        }
        $em->flush();

        return new JsonResponse('ok', Response::HTTP_OK);
    }

    public function asyncRetrievePlannedListsAction(Request $request)
    {
        if (!$request->isXMLHttpRequest()) {
            return new Response('Hijack attempt!', Response::HTTP_BAD_REQUEST );
        }

        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        $allSavedLists = $em->getRepository('Dso\PlannerBundle\Entity\PlannedList')
            ->findBy(array('userId' => $user->getId()));

        $queryBuilder = $em->createQueryBuilder();
        $query = $queryBuilder
        ->select('p.listId')
        ->distinct()
        ->from('Dso\PlannerBundle\Entity\PlannedObject', 'p')
        ->where('p.userId = :userId')
        ->andWhere('p.objId = :objId')
        ->orderBy('p.listId')
        ->getQuery();
        $query->setParameter(':userId', $user->getId());
        $query->setParameter(':objId', $request->get('dsoId'));
        $listsDsoAdded = $query->getResult();

        $jsReady = array();
        if (!empty($allSavedLists)) {
            /** @var PlannedList $list */
            foreach ($allSavedLists as $list) {
                $jsReady[] = array(
                    'listId' => $list->getId(),
                    'listName' => $list->getName(),
                    'dsoOnList' => $this->isDsoOnList($list->getId(), $listsDsoAdded)
                );
            }
        }

        return new JsonResponse($jsReady, Response::HTTP_OK);
    }

    protected function createPlannedListItem($listName, $objId) {
        $user = $this->get('security.context')->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();

        // Create the new Observing list.
        $plannedList = new PlannedList();
        $plannedList->setName($listName)
            ->setUserId($user->getId());
        $em->persist($plannedList);
        $em->flush();

        // Add the selected dso to the Observing list.
        $plannedObject = new PlannedObject();
        $plannedObject->setObjId($objId)
            ->setListId($plannedList->getId())
            ->setUserId($user->getId());
        $em->persist($plannedObject);
        $em->flush();

        return $plannedObject;
    }

    private function isDsoOnList($listId, $listsDsoAlreadyAdded) {
        $dsoOnList = false;
        $listIdsToSearch = array();

        foreach ($listsDsoAlreadyAdded as $listIdAdded) {
            $listIdsToSearch[] = $listIdAdded['listId'];
        }

        if (in_array($listId, $listIdsToSearch)) {
            $dsoOnList = true;
        }

        return $dsoOnList;
    }
}
