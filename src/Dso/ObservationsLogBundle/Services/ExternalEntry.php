<?php

namespace Dso\ObservationsLogBundle\Services;

use Doctrine\ORM\EntityManager;
use Dso\ObservationsLogBundle\Entity\LoggedObject;
use Dso\ObservationsLogBundle\Entity\ObsList;

class ExternalEntry
{
    const DSO_NOT_FOUND = 'Dso Not Found';

    /** @var  EntityManager */
    protected $em;

    /**
     * Checks weather an entry with associated filename and userId already exists.
     *
     * @param string  $filename
     * @param integer $userId
     * @return bool
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function doesEntryExist($filename, $userId) {
        $conn = $this->em->getConnection();
        $sql = "
            SELECT
            `name`
            FROM obs_lists
            WHERE 1
            AND (`name` = :filename)
            AND (`user_id` = :user_id )
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':filename', $filename);
        $stmt->bindValue(':user_id', $userId);
        $stmt->execute();

        $resultsFound = $stmt->fetchAll();
        if (!empty($resultsFound)) {
            return true;
        }

        return false;
    }

    /**
     * @param $details
     * @return mixed
     */
    public function createObservingList($details)
    {
        $defaults = array(
            'userId' => 0,
            'locationId' => 0,
            'name' => '',
            'start' => new \DateTime('now', new \DateTimeZone('UTC')),
            'end' => new \DateTime('now', new \DateTimeZone('UTC')),
            'equipment' => '',
            'conditions' => '',
            'visibilityLevel' => 'private',
            'description' => '',
        );
        $merged = array_merge($defaults, $details);

        $obsList = new ObsList();
        $obsList->setName($merged['name']);
        $obsList->setStart($merged['start']);
        $obsList->setEnd($merged['end']);
        $obsList->setEquipment($merged['equipment']);
        $obsList->setConditions($merged['conditions']);
        $obsList->setUserId($merged['userId']);
        $obsList->setLocationId($merged['locationId']);
        $obsList->setVisibilityLevel($merged['visibilityLevel']);
        $obsList->setDescription($merged['description']);

        $this->em->persist($obsList);
        $this->em->flush();

        return $obsList->getId();
    }

    /**
     * @param array<LoggedObject>  $observedObjects
     * @param integer              $userId
     * @param integer              $listId
     */
    public function persistDsos($observedObjects, $userId, $listId)
    {
        $i = 0;
        $batchSize = 20;
        foreach ($observedObjects as $observedObject) {
            /** @var LoggedObject $observedObject */
            $observedObject->setUserId($userId);
            $observedObject->setListId($listId);
            $this->em->persist($observedObject);
            if (($i % $batchSize) === 0) {
                $this->em->flush($observedObject);
            }
            $i++;
        }
        $this->em->flush(); // Persist objects that did not make up an entire batch.
        $this->em->clear();
    }

    /**
     * @param \Doctrine\ORM\EntityManager $em
     *
     * @return ExternalEntry
     */
    public function setEm($em)
    {
        $this->em = $em;

        return $this;
    }
}
