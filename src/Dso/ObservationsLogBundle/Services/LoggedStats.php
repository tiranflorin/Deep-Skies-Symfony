<?php

namespace Dso\ObservationsLogBundle\Services;

use Doctrine\ORM\EntityManager;

/**
 * Provides stats about logged objects.
 *
 * @package Dso\ObservationsLogBundle\Services
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class LoggedStats {

    /** @var  EntityManager */
    protected $em;

    public function getLatest20Logged($userId) {
        $sql = "
            SELECT
            obj_details.`name` AS objName,
            obj_details.other_name AS objOtherName,
            lists.`name` AS listName,
            logged.comment AS comment,
            logged.observedAt AS observedAt,
            lists.equipment AS equipment
            FROM logged_objects AS logged
            INNER JOIN object AS obj_details
            ON obj_details.id = logged.obj_id
            INNER JOIN obs_lists AS lists
            ON lists.id = logged.list_id
            WHERE logged.user_id = (:userId)
            ORDER BY logged.id DESC
            LIMIT 20;
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
