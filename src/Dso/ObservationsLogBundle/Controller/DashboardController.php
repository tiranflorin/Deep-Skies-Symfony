<?php

namespace Dso\ObservationsLogBundle\Controller;

use Dso\ObservationsLogBundle\Entity\ObsList;
use Dso\ObservationsLogBundle\Services\DiagramData;
use Dso\ObservationsLogBundle\Services\LoggedStats;
use Dso\ObservationsLogBundle\Services\SkylistEntry;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Dso\ObservationsLogBundle\Entity\LoggedObject;
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
        $dsoTypesObserved = $this->getTypesObservedChart();
        $most10Observed = $this->getMost10ObservedChart();
        $observingSessions = $this->getObservingSessionsPerYear();
        /** @var LoggedStats $loggedStats */
        $loggedStats = $this->get('dso_observations_log.logged_stats');
        $latestLogged =  $loggedStats->getLatest20Logged($this->getUser()->getId());

        return $this->render('DsoObservationsLogBundle:Dashboard:index.html.twig', array(
            'chart1' => $dsoTypesObserved,
            'chart2' => $most10Observed,
            'chart3' => $observingSessions,
            'latestLogged' => $latestLogged
        ));
    }

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

        return $this->render('DsoObservationsLogBundle:Dashboard:log.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function logAjaxAction(Request $request) {
        $criteria = $request->get('q', null);
        $em = $this->getDoctrine()->getManager();
        $dsos = $em->getRepository('DsoObservationsLogBundle:DeepSkyItem')
            ->findDsosByName($criteria);

        $data = array();
        if (!empty($dsos)) {
            $i = 0;
            foreach ($dsos as $dso_key => $dsoDetails) {
                $data[$i]['id'] = $dsoDetails->getId();
                $data[$i]['text'] = $dsoDetails->getName();
                $otherName = $dsoDetails->getOtherName();
                if (!empty($otherName)) {
                    $data[$i]['text'] = $dsoDetails->getOtherName() . ' (' . $dsoDetails->getName() . ')';
                }
                $i++;
            }
        }

        return new JsonResponse($data);
    }

    /**
     * @return Highchart
     */
    protected function getTypesObservedChart() {
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

        return $this->setUpTypesObservedChart('DSOs by Category', 'Observed deep sky objects by category (all time)', $dataToRender);
    }

    /**
     * @return Highchart
     */
    protected function getMost10ObservedChart() {
        $values = array();
        $dataToRender = array();
        /** @var DiagramData $diagramData */
        $diagramData = $this->get('dso_observations_log.diagram_data');
        $mostObserved =  $diagramData->getMost10Observed($this->getUser()->getId());

        foreach ($mostObserved as $item) {
            $name = $item['name'];
            if (!empty($item['other_name'])) {
                $name = $item['other_name'] . ' (' . $name . ')';
            }
            $values['name'] = $name;
            $values['data'] = array( (int) $item['nb_times']);

            array_push($dataToRender, $values);
        }

        return $this->setUpMost10ObservedChart('Most 10 observed objects', '10 most often watched objects', $dataToRender);
    }

    /**
     * @return Highchart
     */
    protected function getObservingSessionsPerYear() {

        $dataToRender = array();
        /** @var DiagramData $diagramData */
        $diagramData = $this->get('dso_observations_log.diagram_data');
        $sessions =  $diagramData->getSessionsPerYear($this->getUser()->getId());

        foreach ($sessions as $session) {
            $values = array($session['corresponding_year'], (int) $session['sessions_per_year']);
            array_push($dataToRender, $values);
        }

        return $this->setUpSessionsPerYear('Observing sessions per year', 'Observing sessions/year', $dataToRender);
    }

    /**
     * @param string $name
     * @param string $title
     * @param array  $dataToRender
     *
     * @return Highchart
     */
    private function setUpTypesObservedChart($name, $title, $dataToRender) {
        $ob = new Highchart();
        $ob->chart->renderTo('dso_types_observed_chart');
        $ob->chart->type('pie');
        $ob->chart->options3d(array(
            'enabled' => true,
            'alpha' => 45,
            'beta' => 0
        ));
        $ob->title->text($title);
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
        $ob->plotOptions->pie(array('allowPointSelect' => true, 'cursor' => 'pointer', 'depth' => 35));
        $ob->tooltip->headerFormat('<span style="font-size:11px">{series.name}</span><br>');
        $ob->tooltip->pointFormat('<span style="color:{point.color}">{point.name}</span>: <b>{point.y:.2f}%</b> of total<br/>');
        $ob->series(
            array(
                array(
                    'name' => $name,
                    'colorByPoint' => true,
                    'data' => $dataToRender
                )
            )
        );

        return $ob;
    }

    /**
     * @param string $name
     * @param string $title
     * @param array  $dataToRender
     *
     * @return Highchart
     */
    private function setUpMost10ObservedChart($name, $title, $dataToRender) {
        $ob = new Highchart();
        $ob->chart->renderTo('most_10observed_chart');
        $ob->chart->type('bar');
        $ob->title->text($title);
        $ob->subtitle->text('');
        $ob->plotOptions->bar(array('dataLabels' => array('enabled' => true)));
        $xAxis = array(
            'categories' => array(
                ''
            ),
            'title' => array(
                'text' => null
            )
        );
        $yAxis = array(
            'min' => 0,
            'title' => array(
                'text' => 'How many times they were seen'
            ),
            'labels' => array(
                'overflow' => 'justify'
            )
        );
        $ob->xAxis($xAxis);
        $ob->yAxis($yAxis);
        $ob->tooltip->valueSuffix('{point.name} times');
        $ob->series($dataToRender);

        return $ob;
    }

    /**
     * @param string $name
     * @param string $title
     * @param array  $dataToRender
     *
     * @return Highchart
     */
    private function setUpSessionsPerYear($name, $title, $dataToRender) {
        $ob = new Highchart();
        $ob->chart->renderTo('sessions_per_year_chart');
        $ob->chart->type('column');
        $ob->title->text($title);
        $ob->subtitle->text('');
        $ob->plotOptions->series(array('dataLabels' => array(
            'enabled' => true
        )));
        $xAxis = array(
            'type' => 'category',
            'labels' => array(
                'rotation' => -45
            )
        );
        $yAxis = array(
            'min' => 0,
            'title' => array(
                'text' => 'Number of observing sessions'
            ),
        );
        $ob->xAxis($xAxis);
        $ob->yAxis($yAxis);
        $ob->tooltip->pointFormat('{point.y} sessions logged.');
        $ob->series(array(array(
            'name' => 'Years',
            'data' => $dataToRender))
        );

        return $ob;
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
                'start' => $data->getStart(),
                'end' => $data->getEnd(),
                'equipment' => $data->getEquipment(),
                'conditions' => $data->getConditions(),
            )
        );

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
}
