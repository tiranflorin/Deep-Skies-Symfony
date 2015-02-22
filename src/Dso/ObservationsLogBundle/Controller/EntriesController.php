<?php

namespace Dso\ObservationsLogBundle\Controller;

use Dso\ObservationsLogBundle\Entity\SkylistObject;
use Dso\ObservationsLogBundle\Services\SkylistEntry;
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
        $form = $this->createFormBuilder()
            ->add('skylist_file', 'file', array('label' => 'Choose file: '))
            ->add('save', 'submit', array('label' => 'Import', 'attr' => array('class'=>'btn btn-primary')))
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            /** @var SkylistEntry $skylistService */
            $skylistService = $this->get('dso_observations_log.skylist_entry');
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->getData('skylist_file');
            //TODO: validate the file (extension .skylist)
            $uploadedFile = reset($uploadedFile);
            $content = file_get_contents($uploadedFile->getPath() . '/' . $uploadedFile->getFilename());
            /** @var array<Dso\ObservationsLogBundle\Entity\SkylistObject> $observedObjects */
            $observedObjects = $skylistService->parseContent($content);

            $i = 0;
            $batchSize = 20;
            foreach ($observedObjects as $observedObject) {
                /** @var SkylistObject $observedObject */
                $observedObject->setUserName($this->getUser());
                $em->persist($observedObject);
                if (($i % $batchSize) === 0) {
                    $em->flush();
                }
                $i++;
            }
            $em->flush(); //Persist objects that did not make up an entire batch
            $em->clear();

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
}
