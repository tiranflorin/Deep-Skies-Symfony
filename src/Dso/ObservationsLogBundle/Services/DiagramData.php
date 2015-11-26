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
                `type`,
                COUNT(*) AS nb_times
            FROM object
            WHERE id IN(
              SELECT DISTINCT obj_id
              FROM logged_objects
              WHERE user_id = (:userId)
            )
            AND `type` != ''
            GROUP BY `type`
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
