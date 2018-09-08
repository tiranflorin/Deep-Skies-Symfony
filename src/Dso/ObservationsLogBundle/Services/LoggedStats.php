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
            obj_details.name as `name`,
            obj_details.cat1 as `cat1`,
            obj_details.id1 as `id1`,
            obj_details.cat2 as `cat2`,
            obj_details.id2 as `id2`,
            lists.`name` AS listName,
            logged.comment AS comment,
            logged.observedAt AS observedAt,
            lists.equipment AS equipment,
            location.time_zone as timezone
            FROM logged_objects AS logged
            INNER JOIN object AS obj_details
            ON obj_details.id = logged.obj_id
            INNER JOIN obs_lists AS lists
            ON lists.id = logged.list_id
            INNER JOIN `observing_sites` AS location
            ON `lists`.`location_id` = `location`.`id`
            WHERE logged.user_id = (:userId)
            ORDER BY logged.id DESC
            LIMIT 20;
        ";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $results = $stmt->fetchAll();

        return $this->alterObjectsDateTimeForDisplay($results, $results[0]['timezone']);
    }

    public function getUniqueObjectsCount($userId) {
        $sql = "
            SELECT COUNT(DISTINCT obj_id) FROM logged_objects
            WHERE user_id = (:userId);
        ";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $results = $stmt->fetchColumn();

        return $results;
    }

    public function getUniqueObsSessionsCount($userId) {
        $sql = "
            SELECT COUNT(DISTINCT list_id) FROM logged_objects
            WHERE user_id = (:userId);
        ";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $results = $stmt->fetchColumn();

        return $results;
    }

    public function getSavedLocationsCount($userId) {
        $sql = "
            SELECT COUNT(*) FROM observing_sites
            WHERE user_id = (:userId);
        ";

        $conn = $this->em->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->bindValue('userId', $userId);
        $stmt->execute();
        $results = $stmt->fetchColumn();

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

    public function alterObjectsDateTimeForDisplay($loggedObjects, $timezone) {
        $format = 'Y-m-d H:i:s';
        $utcTimezone = new \DateTimeZone('UTC');
        $displayTimezone = new \DateTimeZone($timezone);
        foreach ($loggedObjects as &$loggedObject) {
            $dt = \DateTime::createFromFormat($format, $loggedObject['observedAt'], $utcTimezone);
            $dt->setTimezone($displayTimezone);
            $loggedObject['observedAt'] = $dt->format('Y-m-d H:i:s');
        }

        return $loggedObjects;
    }
}
