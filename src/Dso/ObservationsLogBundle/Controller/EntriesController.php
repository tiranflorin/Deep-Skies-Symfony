<?php

namespace Dso\ObservationsLogBundle\Controller;

use Dso\ObservationsLogBundle\Entity\DeepSkyItem;
use Dso\ObservationsLogBundle\Services\SkylistEntry;
use Dso\PlannerBundle\Services\SQL\MySqlService;
use Dso\TimelineBundle\Event\CreateTimelineEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Dso\UserBundle\Entity\User;
use Dso\ObservationsLogBundle\Entity\ObsList;
use Symfony\Component\HttpFoundation\JsonResponse;
use Dso\ObservationsLogBundle\Entity\LoggedObject;

/**
 * Class EntriesController
 *
 * @package Dso\ObservationsLogBundle\Controller
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class EntriesController extends Controller
{
    public function logAction(Request $request) {
        $obsList = new ObsList();
        $form = $this->createFormBuilder($obsList)
            ->add('name', 'text', array('attr' => array('placeholder' => 'Main log entry name')))
            ->add('dsos', 'tetranz_select2entity', array(
                    'multiple' => true,
                    'class' => 'DsoObservationsLogBundle:ObsList',
                    'text_property' => 'dsos',
                    'remote_route' => 'dso_observations_log_log_ajax_user',
                    'page_limit' => 15,
                    'placeholder' => 'Search for a DSO',
                )
            )
            ->add('locationId', 'choice', array(
                'choices'  => $this->buildLocationChoices(),
                'label' => 'Location (Select from observing sites defined on your profile)'
            ))
            ->add('visibilityLevel', 'choice', array(
                'choices'  => array('private' => 'Private', 'public' => 'Public'),
                'label' => 'Visibility Level (Public actions will be made visible for everyone)'
            ))
            ->add('description', 'textarea', array('label' => 'Description (optional)'))
            ->add('start', 'text')
            ->add('end', 'text')
            ->add('equipment', 'text')
            ->add('conditions', 'text')
            ->add('save', 'submit', array('label' => 'Save DSO log entry'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $form = $request->request->get('form');

            $this->logManuallyAddedDsos($data, $form['dsos']);

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Your entry has been saved!'
            );

            return $this->redirectToRoute('dso_observations_log_log');
        }

        return $this->render('DsoObservationsLogBundle:Entries:log.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function logAjaxAction(Request $request) {
        $criteria = $request->get('q', null);
        $em = $this->getDoctrine()->getManager();
        $nameFormatter = $this->get('dso_observations_log.format_dso_name');
        $dsos = $em->getRepository('DsoObservationsLogBundle:DeepSkyItem')
            ->findDsosByName($criteria, 15);

        $data = array();
        if (!empty($dsos)) {
            $i = 0;
            foreach ($dsos as $dso_key => $dsoDetails) {
                $data[$i]['id'] = $dsoDetails->getId();
                $data[$i]['text'] = $nameFormatter->formatDsoName($dsoDetails);
                $i++;
            }
        }

        return new JsonResponse($data);
    }

    public function importExternalAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('skylist_file', 'file', array('label' => 'File: '))
            ->add('description', 'textarea', array('label' => 'Description (optional)'))
            ->add('locationId', 'choice', array(
                'choices'  => $this->buildLocationChoices(),
                'label' => 'Location (Select from observing sites defined on your profile)'
            ))
            ->add('visibilityLevel', 'choice', array(
                'choices'  => array('private' => 'Private', 'public' => 'Public'),
                'label' => 'Visibility Level (Public actions will be made visible for everyone)'
            ))
            ->add('save', 'submit', array('label' => 'Import', 'attr' => array('class'=>'btn btn-primary')))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $data['skylist_file'];
            if ('skylist' !== $uploadedFile->getClientOriginalExtension()) {
                $request->getSession()->getFlashBag()->add(
                    'error',
                    'Invalid file! Only "skylist" entries allowed.'
                );

                return $this->redirect($this->generateUrl('dso_observations_log_entries_import_external'));
            }

            $userId = $this->getUser()->getId();
            $content = file_get_contents($uploadedFile->getPath() . '/' . $uploadedFile->getFilename());
            /** @var SkylistEntry $skylistService */
            $skylistService = $this->get('dso_observations_log.skylist_entry');
            if (TRUE === $skylistService->doesEntryExist($uploadedFile->getClientOriginalName(), $userId)) {
                $request->getSession()->getFlashBag()->add('error', 'Duplicate file! Skylist entry already added.');

                return $this->redirect($this->generateUrl('dso_observations_log_entries_import_external'));
            }

            $parsedContent = $skylistService->parseContent($content);
            /** @var LoggedObject $firstObservedItem */
            $firstObservedItem = reset($parsedContent);
            $listId = $skylistService->createObservingList(array(
                    'name' => $uploadedFile->getClientOriginalName(),
                    'userId' =>  $userId,
                    'start' => $firstObservedItem->getObservedAt(),
                    'end' => $firstObservedItem->getObservedAt(),
                    'locationId' => $data['locationId'],
                    'visibilityLevel' => $data['visibilityLevel'],
                    'description' => $data['description'],
                )
            );

            if ($data['visibilityLevel'] === 'public') {
                $this->get('event_dispatcher')->dispatch(
                    CreateTimelineEvent::CREATE_TIMELINE_EVENT,
                    new CreateTimelineEvent(
                        array(
                            'name' => 'Observation log added (processed from SkySafari list)',
                            'userId' => $userId,
                            'obsListId' => $listId
                        )
                    )
                );
            }

            $skylistService->persistDsos($parsedContent, $userId, $listId);
            $request->getSession()->getFlashBag()->add('notice', 'Your file has been uploaded and processed!');

            return $this->redirect($this->generateUrl('dso_observations_log_entries_import_external'));
        }

        return $this->render('DsoObservationsLogBundle:Entries:import_external.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function viewLoggedAction(Request $request)
    {
        /** @var MySqlService $mysql_service */
        $mysqlService = $this->get('dso_planner.mysql_service');
        $paginator  = $this->get('knp_paginator');
        $sql = "
        SELECT
            `obj`.name as `name`,
            `obj`.cat1 as `cat1`,
            `obj`.id1 as `id1`,
            `obj`.cat2 as `cat2`,
            `obj`.id2 as `id2`,
            `obj`.`type`,
            `obj`.`const`,
            `obj`.`name`,
            `logged`.`comment`,
            `logged`.`observedAt`,
            `obs_lists`.`name` AS 'obsList'
        FROM logged_objects AS `logged`
        LEFT JOIN `object` AS `obj`
            ON `logged`.`obj_id` = `obj`.`id`
        LEFT JOIN `obs_lists`
            ON `logged`.`list_id` = `obs_lists`.`id`
        WHERE `logged`.`user_id` = (?)
        ORDER BY `logged`.`id` DESC
        ";
        $stmt = $mysqlService->getConn()->executeQuery(
            $sql,
            array($this->getUser()->getId()),
            array(\PDO::PARAM_INT)
        );
        $pagination = $paginator->paginate(
            $stmt->fetchAll(),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('DsoObservationsLogBundle:Entries:view_logged.html.twig', array('pagination' => $pagination));
    }

    /**
     * @param ObsList $data
     * @param array   $observedObjects
     */
    private function logManuallyAddedDsos($data, $observedObjects) {
        /** @var SkylistEntry $skylistService */
        $skylistService = $this->get('dso_observations_log.skylist_entry');
        $em = $this->getDoctrine()->getManager();

        $listId = $skylistService->createObservingList(array(
                'name' => $data->getName(),
                'userId' => $this->getUser()->getId(),
                'locationId' => $data->getLocationId(),
                'start' => $data->getStart(),
                'end' => $data->getEnd(),
                'equipment' => $data->getEquipment(),
                'conditions' => $data->getConditions(),
                'visibilityLevel' => $data->getVisibilityLevel(),
                'description' => $data->getDescription(),
            )
        );

        if ($data->getVisibilityLevel() === 'public') {
            $user = $this->get('security.context')->getToken()->getUser();
            $this->get('event_dispatcher')->dispatch(
                CreateTimelineEvent::CREATE_TIMELINE_EVENT,
                new CreateTimelineEvent(
                    array(
                        'name' => 'Observation log added (manual entry)',
                        'userId' => $user->getId(),
                        'obsListId' => $listId
                    )
                )
            );
        }

        $i = 0;
        $batchSize = 20;
        foreach ($observedObjects as $observed) {
            $loggedObject = new LoggedObject();
            $loggedObject->setObjId($observed);
            $loggedObject->setUserId($this->getUser()->getId());
            $loggedObject->setListId($listId);

            $em->persist($loggedObject);
            if (($i % $batchSize) === 0) {
                $em->flush($loggedObject);
            }
            $i++;
        }
        $em->flush(); // Persist objects that did not make up an entire batch.
        $em->clear();
    }

    private function buildLocationChoices() {
        $choices = array();
        $observingSites = array();
        $securityContext = $this->get('security.context');
        /** @var User $user */
        $user = $securityContext->getToken()->getUser();
        $em = $this->getDoctrine()->getManager();
        if (null !== $user->getCurrentObservingSiteId()) {
            $observingSites = $em->getRepository('Dso\UserBundle\Entity\ObservingSite')->findBy(
                array('userId' => $user->getId()),
                array('id' => 'DESC')
            );
        }
        if (!empty($observingSites)) {
            foreach ($observingSites as $site) {
                $choices[$site->getId()] = $site->getName();
            }
        }

        return $choices;
    }
}
