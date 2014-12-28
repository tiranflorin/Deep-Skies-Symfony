<?php

namespace Dso\ObservationsLogBundle\Controller;

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
        return $this->render('DsoObservationsLogBundle:Dashboard:index.html.twig');
    }
}
