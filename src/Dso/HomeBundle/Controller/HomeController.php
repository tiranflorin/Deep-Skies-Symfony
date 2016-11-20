<?php

namespace Dso\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('DsoHomeBundle:Home:index.html.twig');
    }

    public function checkFacebookAction()
    {
        // TODO: Further check why this route is mandatory </login/check-facebook>
    }
}
