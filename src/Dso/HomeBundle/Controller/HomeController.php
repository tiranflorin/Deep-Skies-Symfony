<?php

namespace Dso\HomeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class HomeController extends Controller
{
    public function indexAction()
    {
        return $this->render('DsoHomeBundle:Home:base.html.twig');
    }
}
