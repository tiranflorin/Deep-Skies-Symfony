<?php

namespace Dso\TimelineBundle\Controller;

use Dso\ObservationsLogBundle\Entity\ObsList;
use Dso\PlannerBundle\Services\SQL\MySqlService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

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
        FROM `" . $this->container->getParameter('database_name') . "`.timeline_events AS `timeline`
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
}
