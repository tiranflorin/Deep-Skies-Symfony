<?php

namespace Dso\PlannerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('DsoPlannerBundle:Default:index.html.twig', array('name' => $name));
    }
}
