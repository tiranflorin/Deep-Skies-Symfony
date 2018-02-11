<?php

namespace Dso\SearchBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchController
 *
 * @package Dso\SearchBundle\Controller
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class SearchController extends Controller
{

    /**
     * @TODO: Need to get the image details for the retrieved results!
     *
     * @param Request $req
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $req)
    {
        $criteria = $req->get('keywords', null);
        if (!$criteria) {
            return $this->render('DsoHomeBundle:Home:search_results.html.twig', array(
                '' => ''
            ));
        }

        $em = $this->getDoctrine()->getManager();
        $dsos = $em->getRepository('DsoObservationsLogBundle:DeepSkyItem')
            ->findDsosByName($criteria);

        $paginator = $this->get('knp_paginator');
        $paginatedResults = $paginator->paginate(
            $dsos,
            $req->get('page', 1),
            20
        );
        $paginatedResults->setParam('keywords', $criteria);

        return $this->render('DsoHomeBundle:Home:search_results.html.twig', array(
            'pagination' => $paginatedResults
        ));
    }
}
