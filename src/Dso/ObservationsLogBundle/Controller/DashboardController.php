<?php

namespace Dso\ObservationsLogBundle\Controller;

use Dso\ObservationsLogBundle\Services\DiagramData;
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
}
