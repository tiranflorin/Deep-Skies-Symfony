<?php

namespace Dso\PlannerBundle\Controller;

use Dso\PlannerBundle\Form\PredefinedFilters;
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
        $formPredefinedFilters = $this->createForm(new PredefinedFilters());
        return $this->render('DsoPlannerBundle:Home:index.html.twig', array(
            'settings' => $settings,
            'formPredefinedFilters' => $formPredefinedFilters->createView()
        ));

    }

    public function filterPredefinedAction(Request $request)
    {
        /** @var FilterResults $filterService */
        $filterService = $this->get('dso_planner.filter_results');

        if ($request->getMethod() == 'POST') {
            $form = $this->createForm(new PredefinedFilters());
            $form->handleRequest($request);

            if ($form->isValid()) {
                $filterType = $form->get('filter_type')->getData();

                if($filterType == 'predefined') {
                    if ($form->get('naked_eye')->isClicked() === true)
                        $selection = 'naked_eye';
                    if ($form->get('binoculars')->isClicked() === true)
                        $selection = 'binoculars';
                    if ($form->get('small_telescope')->isClicked() === true)
                        $selection = 'small_telescope';

                    $filterService->setConfigurationDetails($filterType, $selection);
                }
                $results = $filterService->retrieveFilteredData();
                echo 'ceva';
            }
        }
    }

    //TODO: completeaza si metoda asta si creeaza formularul asociat:
    public function filterCustomAction(Request $request)
    {
        /** @var FilterResults $filterService */
        $filterService = $this->get('dso_planner.filter_results');

        if ($request->getMethod() == 'POST') {
            $form = $this->createForm(new CustomFilters());
            $form->handleRequest($request);

            if ($form->isValid()) {
                $filterType = $form->get('filter_type')->getData();

                if($filterType == 'custom') {
                    $const = $form->get('constellation');
                    $magMin = $form->get('magMin');
                    $magMax = $form->get('magMax');
                    $objType = $form->get('objectType');
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
    }
}
