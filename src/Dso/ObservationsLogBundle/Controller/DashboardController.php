<?php

namespace Dso\ObservationsLogBundle\Controller;

use Dso\ObservationsLogBundle\Services\DiagramData;
use Dso\ObservationsLogBundle\Services\LoggedStats;
use Ob\HighchartsBundle\Highcharts\Highchart;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

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
        $uniqueObjectsCount = $loggedStats->getUniqueObjectsCount($this->getUser()->getId());
        $uniqueObsSessionsCount = $loggedStats->getUniqueObsSessionsCount($this->getUser()->getId());
        $savedLocationsCount = $loggedStats->getSavedLocationsCount($this->getUser()->getId());

        return $this->render('DsoObservationsLogBundle:Dashboard:index.html.twig', array(
            'chart1' => $dsoTypesObserved,
            'chart2' => $most10Observed,
            'chart3' => $observingSessions,
            'latestLogged' => $latestLogged,
            'uniqueObjectsCount' => $uniqueObjectsCount,
            'uniqueObsSessionsCount' => $uniqueObsSessionsCount,
            'savedLocationsCount' => $savedLocationsCount
        ));
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
        $dsoNameFormat = $this->get('dso_name_format.twig_extension');
        $mostObserved =  $diagramData->getMost10Observed($this->getUser()->getId());

        foreach ($mostObserved as $item) {
            $values['name'] = $dsoNameFormat->formatDsoName($item);
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
}
