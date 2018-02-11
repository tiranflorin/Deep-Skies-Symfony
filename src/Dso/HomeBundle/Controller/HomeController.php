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

    public function termsAndConditionsAction()
    {
        return $this->render('DsoHomeBundle:Home:terms_and_conditions.html.twig');
    }

    public function checkFacebookAction()
    {
        // TODO: Further check why this route is mandatory </login/check-facebook>
    }
}
