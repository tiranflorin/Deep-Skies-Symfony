<?php

namespace Dso\ObservationsLogBundle\Controller;

use Dso\ObservationsLogBundle\Services\SkylistEntry;
use Dso\PlannerBundle\Services\SQL\MySqlService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class EntriesController
 *
 * @package Dso\ObservationsLogBundle\Controller
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class EntriesController extends Controller
{
    public function importExternalAction(Request $request)
    {
        // TODO: don't allow the import of an existing file.
        $form = $this->createFormBuilder()
            ->add('skylist_file', 'file', array('label' => 'Choose file: '))
            ->add('save', 'submit', array('label' => 'Import', 'attr' => array('class'=>'btn btn-primary')))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->getData('skylist_file');
            //TODO: validate the file (extension .skylist)
            $uploadedFile = reset($uploadedFile);
            $content = file_get_contents($uploadedFile->getPath() . '/' . $uploadedFile->getFilename());

            /** @var SkylistEntry $skylistService */
            $skylistService = $this->get('dso_observations_log.skylist_entry');
            $listId = $skylistService->createObservingList(array(
                    'name' => $uploadedFile->getClientOriginalName(),
                    'userId' => $this->getUser()->getId()
                )
            );
            $skylistService->persistDsos(
                $skylistService->parseContent($content),
                $this->getUser()->getId(),
                $listId
            );
            $request->getSession()->getFlashBag()->add(
                'notice',
                'Your file has been uploaded and processed!'
            );
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
            `obj`.`name`,
            `obj`.`other_name` AS 'otherName',
            `obj`.`type`,
            `obj`.`constellation`,
            `obj`.`name`,
            `logged`.`comment`,
            `logged`.`observedAt`,
            `obs_lists`.`name` AS 'obsList'
        FROM `deep-skies-sym`.logged_objects AS `logged`
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
}
