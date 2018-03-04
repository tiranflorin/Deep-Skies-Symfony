<?php

namespace Dso\SearchBundle\Controller;

use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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

        $externalMatches = $this->getExternalMatches($criteria);
        if (!empty($externalMatches)) {
            $dsos = $this->matchResultsWithInternal($externalMatches);
        } else {
            // Fallback to our own search implementation.
            $dsos = $this->getInternalMatches($criteria);
        }

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

    protected function getInternalMatches($criteria) {
        $em = $this->getDoctrine()->getManager();
        $dsos = $em->getRepository('DsoObservationsLogBundle:DeepSkyItem')
            ->findDsosByName($criteria);

        return $dsos;
    }

    protected function getExternalMatches($criteria) {
        $matches = [];
        $client = new Client();
        $queryParams = [
            'header_json' => 1,
            'q' => $criteria
        ];
        $res = $client->request('GET', $this->container->getParameter('dso_details_external_search_api'), ['query' => $queryParams]);
        $statusCode = (int) $res->getStatusCode();
        if ($statusCode !== Response::HTTP_OK) {
            return $matches;
        }

        $contentType = $res->getHeader('content-type');
        if (!in_array('application/json', $contentType)) {
            return $matches;
        }

        $body = $res->getBody();
        $matches = json_decode($body->getContents());

        return $matches;
    }

    protected function matchResultsWithInternal($results) {
        $dsoIds = [];
        foreach ($results as $dso) {
            if (property_exists($dso, 'uid')) {
                array_push($dsoIds, $dso->uid);
            }
        }

        $em = $this->getDoctrine()->getManager();
        $dsos = $em->getRepository('DsoObservationsLogBundle:DeepSkyItem')
            ->findDsosByIds($dsoIds);

        return $dsos;
    }
}
