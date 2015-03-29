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
    public function indexAction(Request $request)
    {
        $searchTerm = $request->get('searchTerm');
        if (empty($searchTerm)) {
            $request->getSession()->getFlashBag()->add(
                'warning',
                'Your search enquiry was invalid!'
            );

            return $this->render('DsoHomeBundle:Home:index.html.twig');
        }

        $term = trim($request->get('searchTerm', ''));
        $conn = $this->get('database_connection');
        $sql = "
            SELECT
                *
            FROM object
            WHERE 1
            AND (name LIKE :searchTerm OR other_name LIKE :searchTerm OR notes LIKE :searchTerm OR ngc_description LIKE :searchTerm)
            ORDER BY mag ASC
            LIMIT 100
        "; // TODO: implement pagination and remove the 100 elements limit from the query.

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('searchTerm', trim("%$term%"));
        $stmt->execute();
        $resultsFound = $stmt->fetchAll();

        return $this->render('DsoSearchBundle:Search:index.html.twig', array(
            'term' => $term,
            'resultsFound' => $resultsFound
            )
        );
    }
}
