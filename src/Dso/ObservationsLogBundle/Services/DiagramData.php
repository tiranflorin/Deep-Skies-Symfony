<?php

namespace Dso\ObservationsLogBundle\Services;

use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\InvalidArgumentException;

/**
 * Provides different stats for generating diagrams.
 *
 * @package Dso\ObservationsLogBundle\Services
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class DiagramData {

    /** @var  EntityManager */
    protected $em;

    public function getDsoTypesObserved($userId) {
        if (!is_numeric($userId)) {
            throw new InvalidArgumentException('Invalid userId provided.', Response::HTTP_CONFLICT);
        }

        $sql = "
            SELECT
                object.`type`,
                COUNT(*) AS nb_times
            FROM object JOIN (SELECT DISTINCT obj_id FROM logged_objects WHERE user_id = (:userId)) as logged
            ON object.id = logged.obj_id
                AND object.`type` != ''
            GROUP BY `type`
        ";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }

    public function getMost10Observed($userId) {
        $sql = "
            SELECT
            `name`,
            other_name,
            COUNT(obj_id) AS nb_times
            FROM logged_objects AS logged
            INNER JOIN object AS obj_details
            ON obj_details.id = logged.obj_id
            WHERE user_id = (:userId)
            GROUP BY obj_id
            ORDER BY nb_times DESC
            LIMIT 10;
        ";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }

    public function getSessionsPerYear($userId) {
        $sql = "
            SELECT
            COUNT(*) as sessions_per_year,
            YEAR(start) as corresponding_year
            FROM `deep-skies-sym`.obs_lists
            WHERE user_id = (:userId) and YEAR(start) != 0
            GROUP BY corresponding_year
            ORDER BY corresponding_year DESC
        ";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $results;
    }

    /**
     * @param EntityManager $em
     *
     * @return DiagramData
     */
    public function setEm($em)
    {
        $this->em = $em;

        return $this;
    }
}
