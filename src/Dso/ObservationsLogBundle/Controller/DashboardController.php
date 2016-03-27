<?php

namespace Dso\ObservationsLogBundle\Controller;

use Dso\ObservationsLogBundle\Entity\ManualObsList;
use Dso\ObservationsLogBundle\Services\DiagramData;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class DashboardController
 *
 * @package Dso\ObservationsLogBundle\Controller
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class DashboardController extends Controller
{
    public function indexAction()
    {
        $total = 0;
        $values = array();
        $percentage = array();
        $dataToRender = array();
        /** @var DiagramData $diagramData */
        $diagramData = $this->get('dso_observations_log.diagram_data');
        $dsoTypesObserved =  $diagramData->getDsoTypesObserved($this->getUser()->getId());

        foreach ($dsoTypesObserved as $item) {
            $values[$item['type']] = $item['nb_times'];
            $total += $item['nb_times'];
        }
        foreach ($values as $key => $value) {
            $percentage[$key] = $value / $total;
        }
        foreach ($percentage as $type => $percent) {
            $val = $percent * 100;
            $dataToRender[] = array($type, (float) number_format($val, 2));
        }

        $ob = new Highchart();
        $ob->chart->renderTo('piechart');
        $ob->chart->type('pie');
        $ob->title->text('Observed deep sky objects by category (all time)');
        $ob->subtitle->text('Click a slice to bring to focus.');
        $ob->plotOptions->series(
            array(
                'dataLabels' => array(
                    'enabled' => true,
                    'format' => '<b>{point.name}</b>: {point.percentage:.1f} %',
                    'style' => "style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }"
                )
            )
        );
        $ob->plotOptions->pie(
            array(
                'allowPointSelect' => true,
                'cursor' => 'pointer'
            )
        );
        $ob->tooltip->headerFormat('<span style="font-size:11px">{series.name}</span><br>');
        $ob->tooltip->pointFormat('<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>');
        $ob->series(
            array(
                array(
                    'name' => 'DSOs by Category',
                    'colorByPoint' => true,
                    'data' => $dataToRender
                )
            )
        );

        return $this->render('DsoObservationsLogBundle:Dashboard:index.html.twig', array(
            'chart' => $ob
        ));
    }

    public function logAction(Request $request) {
        $obsList = new ManualObsList();
        $form = $this->createFormBuilder($obsList)
            ->add('name', 'text', array('attr' => array('placeholder' => 'Main log entry name')))
            ->add('dsos', 'tetranz_select2entity', array(
                'multiple' => true,
                'class' => 'DsoObservationsLogBundle:ManualObsList',
                'text_property' => 'dsos',
                'remote_route' => 'dso_observations_log_log_ajax_user',
                'page_limit' => 15,
                'placeholder' => 'Search for a DSO',
                )
            )
            ->add('period', 'text')
            ->add('equipment', 'text')
            ->add('conditions', 'text')
            ->add('save', 'submit', array('label' => 'Save DSO log entry'))
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $form = $request->request->get('form');
            $nbObserved = $form['dsos'];

            $em = $this->getDoctrine()->getManager();
            $repository = $this->getDoctrine()->getRepository('DsoObservationsLogBundle:Object');

            foreach ($nbObserved as $observed) {
                $obsListClean = clone $data;
                $observedObject = $repository->find($observed);
                $obsListClean->setDsoObject($observedObject);
                $em->persist($obsListClean);
            }

            $em->flush();

            $request->getSession()->getFlashBag()->add(
                'notice',
                'Your entry has been saved!'
            );

            return $this->redirectToRoute('dso_observations_log_log');
        }

        return $this->render('DsoObservationsLogBundle:Dashboard:log.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function logAjaxAction(Request $request) {
        $criteria = $request->get('q', null);
        $em = $this->getDoctrine()->getManager();
        $dsos = $em->getRepository('DsoObservationsLogBundle:Object')
            ->findDsosByName($criteria);

        $data = array();
        if (!empty($dsos)) {
            $i = 0;
            foreach ($dsos as $dso_key => $dso_details) {
                $data[$i]['id'] = $dso_details->getId();
                $data[$i]['text'] = $dso_details->getName();
                $i++;
            }
        }

        return new JsonResponse($data);
    }
}
