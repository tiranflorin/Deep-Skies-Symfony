<?php

namespace Dso\HomeBundle\Controller;

use Dso\PlannerBundle\Services\FilterResults;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('DsoHomeBundle:Home:index.html.twig');
    }

    public function termsAndAgreementAction()
    {
        return $this->render('DsoHomeBundle:Home:terms_and_agreement.html.twig');
    }

    public function checkFacebookAction()
    {
        // TODO: Further check why this route is mandatory </login/check-facebook>
    }

    public function searchAction(Request $request)
    {
        /** @var FilterResults $filterService */
        $filterService = $this->get('dso_planner.filter_results');
        $keywords = $request->get('keywords', '');
        if (empty($keywords)) {
            return $this->render('DsoHomeBundle:Home:search_results.html.twig', array(
                '' => ''
            ));
        }

        $filterService->setConfigurationDetails('object', null, null);

        $paginatedResults = $filterService->retrieveSearchResults($request->get('page', 1), $keywords);

        return $this->render('DsoHomeBundle:Home:search_results.html.twig', array(
            'pagination' => $paginatedResults
        ));
    }
}
