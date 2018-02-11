<?php

namespace Dso\TimelineBundle\Controller;

use Doctrine\ORM\AbstractQuery;
use Dso\ObservationsLogBundle\Entity\ObsList;
use Dso\PlannerBundle\Services\SQL\MySqlService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Connection;

class TimelineController extends Controller
{
    public function indexAction(Request $request)
    {
        /** @var MySqlService $mysql_service */
        $mysqlService = $this->get('dso_planner.mysql_service');
        $paginator  = $this->get('knp_paginator');
        $sql = "
        SELECT
            fos_user.username,
            timeline.added_on as 'addedOn',
            timeline.name as 'eventName',
            obs_lists.id as 'obsListId',
            obs_lists.name as 'obsListName',
            obs_lists.equipment as 'obsEquipment',
            obs_lists.conditions as 'obsConditions'
        FROM `timeline_events` AS `timeline`
        LEFT JOIN `obs_lists`
            ON `timeline`.`obs_list_id` = `obs_lists`.`id`
        LEFT JOIN `fos_user`
            ON `timeline`.`user_id` = `fos_user`.`id`
        ORDER BY `timeline`.`id` DESC
        ";
        $stmt = $mysqlService->getConn()->executeQuery(
            $sql
        );
        $pagination = $paginator->paginate(
            $stmt->fetchAll(),
            $request->query->getInt('page', 1),
            20
        );

        return $this->render('DsoTimelineBundle:Timeline:index.html.twig',
            array('pagination' => $pagination)
        );
    }

    public function obsListItemsAction($obsListId)
    {
        /** @var ObsList $observingList */
        $observingList = $this->getDoctrine()
            ->getRepository('DsoObservationsLogBundle:ObsList')
            ->find($obsListId);
        if (!$observingList) {
            throw $this->createNotFoundException(
                'No observing list found!'
            );
        }
        if ($observingList->getVisibilityLevel() === 'private') {
            throw $this->createNotFoundException(
                'No observing list found!'
            );
        }

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT d
            FROM DsoObservationsLogBundle:LoggedObject l
            JOIN DsoObservationsLogBundle:DeepSkyItem d
              WITH d.id = l.objId
            WHERE l.listId = :list_id'
        )->setParameter('list_id', $obsListId);

        $loggedObjects = $query->getResult();

        return $this->render(
            'DsoTimelineBundle:Timeline:objects_attached_to_list.html.twig',
            array('loggedObjects' => $loggedObjects)
        );
    }

    public function obsListItemsTextFormatAction($obsListId)
    {
        /** @var ObsList $observingList */
        $observingList = $this->getDoctrine()
            ->getRepository('DsoObservationsLogBundle:ObsList')
            ->find($obsListId);
        if (!$observingList) {
            throw $this->createNotFoundException(
                'No observing list found!'
            );
        }
        if ($observingList->getVisibilityLevel() === 'private') {
            throw $this->createNotFoundException(
                'No observing list found!'
            );
        }

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT d
            FROM DsoObservationsLogBundle:LoggedObject l
            JOIN DsoObservationsLogBundle:DeepSkyItem d
              WITH d.id = l.objId
            WHERE l.listId = :list_id'
        )->setParameter('list_id', $obsListId);

        $loggedObjects = $query->getResult();

        return $this->render(
            'DsoTimelineBundle:Timeline:objects_attached_to_list.html.twig',
            array('loggedObjects' => $loggedObjects)
        );
    }

    public function shareableObsListAction($observingListId) {
        /** @var ObsList $observingList */
        $observingList = $this->getDoctrine()
            ->getRepository('DsoObservationsLogBundle:ObsList')
            ->find($observingListId);
        if (!$observingList) {
            throw $this->createNotFoundException(
                'No observing list found!'
            );
        }
        if ($observingList->getVisibilityLevel() === 'private') {
            throw $this->createNotFoundException(
                'No observing list found!'
            );
        }

        /** @var MySqlService $mysql_service */
        $mysqlService = $this->get('dso_planner.mysql_service');
        $sql = "
        SELECT
            fos_user.username,
            fos_user.id,
            timeline.added_on as 'addedOn',
            timeline.name as 'eventName',
            obs_lists.id as 'obsListId',
            obs_lists.name as 'obsListName',
            obs_lists.equipment as 'obsEquipment',
            obs_lists.conditions as 'obsConditions'
        FROM `timeline_events` AS `timeline`
        LEFT JOIN `obs_lists`
            ON `timeline`.`obs_list_id` = `obs_lists`.`id`
        LEFT JOIN `fos_user`
            ON `timeline`.`user_id` = `fos_user`.`id`
        WHERE `obs_lists`.`id` = $observingListId
        ORDER BY `timeline`.`id` DESC
        ";
        $stmt = $mysqlService->getConn()->executeQuery($sql);
        $results = $stmt->fetchAll();

        $em = $this->getDoctrine()->getManager();
        $query = $em->createQuery(
            'SELECT d.id
            FROM DsoObservationsLogBundle:LoggedObject l
            JOIN DsoObservationsLogBundle:DeepSkyItem d
              WITH d.id = l.objId
            WHERE l.listId = :list_id'
        )->setParameter('list_id', $observingListId);

        $loggedObjects = $query->getResult(AbstractQuery::HYDRATE_ARRAY);
        $objIds = array_map(function($loggedObject) {
            return $loggedObject['id'];
        }, $loggedObjects);

        $sql = "
        SELECT DISTINCT
            logged.observedAt,
            logged.comment,
            source.name as `name`,
            source.cat1 as `cat1`,
            source.id1 as `id1`,
            source.cat2 as `cat2`,
            source.id2 as `id2`,
            source.type as `ObjType`,
            source.const as `Constellation`,
            source.mag as `ObjMagnitude`,
            source.notes as `Other_notes`,
            IFNULL(img.thumb, 'no_image_available.png') as `thumb`,
            IFNULL(img.full_size, 'no_image_available_large.png') as `full_size`
        FROM  `logged_objects` as logged
        LEFT JOIN `object`  as source
            ON logged.obj_id = source.id
        LEFT JOIN `image_paths` as img
            ON img.object_id = source.id

        WHERE
            logged.list_id = ? AND
            logged.obj_id IN (?)
        ";
        $stmt = $mysqlService->getConn()->executeQuery(
            $sql,
            array(
                $observingListId,
                $objIds
            ),
            array(\PDO::PARAM_INT, Connection::PARAM_INT_ARRAY)
        );

        $loggedObjects = $stmt->fetchAll();

        return $this->render(
            'DsoTimelineBundle:Timeline:shareable_observing_list.html.twig',
            array(
                'event' => reset($results),
                'loggedObjects' => $loggedObjects
            )
        );
    }
}
