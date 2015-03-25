<?php

namespace Dso\ObservationsLogBundle\Controller;

use Doctrine\ORM\EntityRepository;
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
            $skylistService->persistDsos(
                $skylistService->parseContent($content),
                $uploadedFile->getClientOriginalName(),
                $this->getUser()->getUsername()
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

    public function viewLoggedAction()
    {
        $em = $this->getDoctrine()->getManager();
        /** @var EntityRepository $repository */
        $repository = $em->getRepository('DsoObservationsLogBundle:SkylistObject');
        $dsos = $repository->findBy(array('userName' => $this->getUser()->getUsername()), array('observingSessionName' => 'ASC'));
        // TODO: add group by observing session name
        return $this->render('DsoObservationsLogBundle:Entries:view_logged.html.twig', array(
            'dsos' => $dsos,
        ));
    }
}
