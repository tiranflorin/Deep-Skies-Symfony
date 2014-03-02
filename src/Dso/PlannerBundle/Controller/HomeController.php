<?php

namespace Dso\PlannerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Dso\PlannerBundle\Services\FilterResults;

class HomeController extends Controller
{
    public function indexAction()
    {
        //TODO complex logic here, (e.g. check if the user has custom settings)
        $dateTime = new \DateTime();
        $formattedDateTime = $dateTime->format('Y-m-d H:i:s');
        $settings = array(
            'location' => 'Cluj Napoca, Romania, (23.45 E, 45.23 N)',
            'datetime' => $formattedDateTime,
            'timezone' => 'GMT +2:00'
        );
        return $this->render('DsoPlannerBundle:Home:index.html.twig', array('settings' => $settings));
    }

    public function filterAction()
    {
        /** @var FilterResults $filterService */
        $filterService = $this->get('dso_planner.filter_results');

        $request = Request::createFromGlobals();
        $filterType = $request->request->get('filter_type');

        if($filterType == 'predefined') {
            $selection = $request->request->get('predefined_selection');
            $filterService->setConfigurationDetails($filterType, $selection);
        }

        if($filterType == 'custom') {
            $const = $request->request->get('constellation');
            $magMin = $request->request->get('magMin');
            $magMax = $request->request->get('magMax');
            $objType = $request->request->get('objectType');
            $selection = array(
                'constellation' => $const,
                'magMin' => $magMin,
                'magMax' => $magMax,
                'objType' => $objType
            );
            $filterService->setConfigurationDetails($filterType, $selection);
        }

        $results = $filterService->retrieveFilteredData();
        echo 'ceva';
    }
}
