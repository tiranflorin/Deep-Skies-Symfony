<?php

namespace Dso\ObservationsLogBundle\Services;

use Doctrine\ORM\EntityManager;
use Dso\ObservationsLogBundle\Entity\LoggedObject;
use Dso\ObservationsLogBundle\Entity\ObsList;
use Dso\ObservationsLogBundle\Entity\SkylistObject;

/**
 * Processes a SkySafari observing list entry
 * (.skylist) having the following format:
 *
 *      SkySafariObservingListVersion=3.0
 *      SortedBy=Constellation
 *      SkyObject=BeginObject
 *          ObjectID=4,0,25
 *          CommonName=Andromeda Galaxy
 *          CatalogNumber=M 31
 *          CatalogNumber=NGC 224
 *          CatalogNumber=UGC 454
 *          CatalogNumber=PGC 2557
 *          CatalogNumber=MCG 7-2-16
 *          CatalogNumber=CGCG 535-17
 *          DateObserved=2.456892452531104e+06
 *      EndObject=SkyObject
 *      ...
 *      SkyObject=BeginObject
 *          ObjectID=4,0,973
 *          CatalogNumber=NGC 404
 *          CatalogNumber=UGC 718
 *          CatalogNumber=PGC 4126
 *          CatalogNumber=MCG 6-3-18
 *          CatalogNumber=CGCG 520-20
 *          CatalogNumber=IRAS 01066+3527
 *          DateObserved=2.456892419165283e+06
 *      EndObject=SkyObject
 *
 * @package Dso\ObservationsLogBundle\Services
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class SkylistEntry {

    const DSO_NOT_FOUND = 'Dso Not Found';

    /** @var  string $content */
    protected $content;

    /** @var  EntityManager */
    protected $em;

    /**
     * Processes the .skylist content
     *
     * @param string $content
     *
     * @return array<Dso\ObservationsLogBundle\Entity\SkylistObject>
     */
    public function parseContent($content)
    {
        $this->content = $content;
        $this->removeHeaderInfo();
        $pieces = $this->splitIntoObservedObjects();
        $loggedObjects = $this->createLoggedObjectsFromArray($pieces);

        return $loggedObjects;
    }

    /**
     * Removes additional details like:
     *  "SkySafariObservingListVersion=3.0
     *   SortedBy=Constellation"
     */
    public function removeHeaderInfo()
    {
        $this->content = strstr($this->content, 'SkyObject=BeginObject');
    }

    /**
     * Splits the content string using a delimiter.
     *
     * @return array
     */
    public function splitIntoObservedObjects()
    {
        $pieces = explode('SkyObject=BeginObject', $this->content);

        return array_filter($pieces);
    }

    /**
     * Creates a list of observed objects.
     *
     * @param array $skylistContent
     *
     * @return array
     */
    public function createFromArray($skylistContent)
    {
        $observedObjectsList = array();
        foreach ($skylistContent as $item) {
            $skylistObject = new SkylistObject();
            $content = strstr($item, 'EndObject=SkyObject', true);
            $pieces = array_filter(explode("\n\t", $content));
            foreach ($pieces as $itemProperty) {
                $this->extractDetailsFromSkylistItem($skylistObject, $itemProperty);
            }
            $observedObjectsList[] = $skylistObject;
        }

        return $observedObjectsList;
    }

    /**
     * Creates a list of observed objects.
     *
     * @param array $skylistContent
     *
     * @return array
     */
    public function createLoggedObjectsFromArray($skylistContent)
    {
        $observedObjectsList = array();
        foreach ($skylistContent as $item) {
            $skylistObject = new LoggedObject();
            $dso_id = $this->retrieveDsoId($item);

            // Create a new entry in the system if the object hasn't been found.
            if ($dso_id === SkylistEntry::DSO_NOT_FOUND) {
                $dso_id = $this->insertNewDso($item);
            }
            $skylistObject->setObjId($dso_id);
            $content = strstr($item, 'EndObject=SkyObject', true);
            $pieces = array_filter(explode("\n\t", $content));
            $date = new \DateTime('now', new \DateTimeZone('UTC'));
            foreach ($pieces as $itemProperty) {
                if (strpos($itemProperty, 'DateObserved=') !== false) {
                    $val = trim(preg_replace('/\s+/', ' ', $itemProperty)); // Removes the extra \n
                    $number = (float) substr($val, 13, strlen($val)); // Cast the scientific notation string to float.
                    $timestamp = ($number - 2440587.5) * 86400; // Convert it from Julian notation to Unix timestamp.
                    $date->setTimestamp($timestamp);
                    $skylistObject->setObservedAt($date);
                }
                if (strpos($itemProperty, 'Comment=') !== false) {
                    trim($itemProperty);
                    $skylistObject->setComment(substr($itemProperty, strlen('Comment=')));
                }
            }
            $observedObjectsList[] = $skylistObject;
        }

        return $observedObjectsList;
    }

    /**
     * Adds a new observing list entry.
     *
     * @param $details
     *
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
     * @return SkylistEntry
     */
    public function setEm($em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * Retrieve a dso_id from the system (object table for now).
     *
     * @param array $item
     *
     * @return mixed
     */
    public function retrieveDsoId($item) {
        $commonName = '';
        $otherName = '';
        $content = strstr($item, 'EndObject=SkyObject', true);
        $pieces = array_filter(explode("\n\t", $content));

        foreach ($pieces as $itemProperty) {
            if (strpos($itemProperty, 'CommonName=') !== false) {
                trim($itemProperty);
                $commonName = substr($itemProperty, strlen('CommonName='));
            }
            if (strpos($itemProperty, 'CatalogNumber=NGC') !== false) {
                trim($itemProperty);
                $commonName = substr($itemProperty, strlen('CatalogNumber='));
                $otherName = substr($itemProperty, strlen('CatalogNumber='));
            }
            if (strpos($itemProperty, 'CatalogNumber=M ') !== false) {
                trim($itemProperty);
                $otherName = substr($itemProperty, strlen('CatalogNumber='));
            }
            if (strpos($itemProperty, 'CatalogNumber=IC') !== false) {
                trim($itemProperty);
                $otherName = substr($itemProperty, strlen('CatalogNumber='));
            }
        }

        if ($commonName != '') {
            return $this->findDsoByCommonName($commonName);
        }

        if ($otherName != '') {
            return $this->findDsoByOtherName($otherName);
        }

        return SkylistEntry::DSO_NOT_FOUND;
    }

    /**
     * Saves a new DSO in our system, with SkySafari item details available.
     *
     * @param $item
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertNewDso($item) {
        $content = strstr($item, 'EndObject=SkyObject', true);
        $pieces = array_filter(explode("\n\t", $content));
        $skylistObject = new SkylistObject();

        foreach ($pieces as $itemProperty) {
            $this->extractDetailsFromSkylistItem($skylistObject, $itemProperty);
        }
        $conn = $this->em->getConnection();
        $sql = "INSERT INTO object (name, other_names) VALUES (:common_name, :other_names)";
        $stmt = $conn->prepare($sql);

        if ($skylistObject->getCommonName() != ''
            || $skylistObject->getCatalogNumberIc() != ''
            || $skylistObject->getCatalogNumberNgc() != ''
            || $skylistObject->getCatalogNumberMessier() != '') {

            $otherName = '';
            if ($skylistObject->getCatalogNumberIc() != '') {
                $otherName = $skylistObject->getCatalogNumberIc();
            }
            if ($skylistObject->getCatalogNumberNgc() != '') {
                $otherName = $skylistObject->getCatalogNumberNgc();
            }
            if ($skylistObject->getCatalogNumberMessier() != '') {
                $otherName = $skylistObject->getCatalogNumberMessier();
            }

            $commonName = '';
            if ($skylistObject->getCommonName() !== NULL) {
                $commonName = $skylistObject->getCommonName();
            }
            $stmt->bindValue('common_name', str_replace(' ', '', $commonName));
            $stmt->bindValue('other_names', str_replace(' ', '', $otherName));
            $stmt->execute();

            // TODO: find a faster way (without an extra query) to get the "inserted_id";
            if ($commonName != '') {
                return $this->findDsoByCommonName($commonName);
            }

            if ($otherName != '') {
                return $this->findDsoByOtherName($otherName);
            }
        }

        return -1;
    }

    /**
     * @param SkylistObject $skylistObject
     * @param string        $item
     */
    public function extractDetailsFromSkylistItem(SkylistObject $skylistObject, $item) {
        if (strpos($item, 'CommonName=') !== false) {
            trim($item);
            $skylistObject->setCommonName(substr($item, strlen('CommonName=')));
        }
        if (strpos($item, 'CatalogNumber=NGC') !== false) {
            trim($item);
            $skylistObject->setCatalogNumberNgc(substr($item, strlen('CatalogNumber=')));
        }
        if (strpos($item, 'CatalogNumber=M ') !== false) {
            trim($item);
            $skylistObject->setCatalogNumberMessier(substr($item, strlen('CatalogNumber=')));
        }
        if (strpos($item, 'CatalogNumber=IC') !== false) {
            trim($item);
            $skylistObject->setCatalogNumberIc(substr($item, strlen('CatalogNumber=')));
        }
        if (strpos($item, 'DateObserved=') !== false) {
            trim($item);
            //TODO: Change the string value(2.456892419165283e+06) to DateTime
            $skylistObject->setDateObserved(substr($item, strlen('DateObserved=')));
        }
        if (strpos($item, 'Comment=') !== false) {
            trim($item);
            $skylistObject->setComment(substr($item, strlen('Comment=')));
        }
    }

    /**
     * @param string $commonName
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findDsoByCommonName($commonName) {
        $conn = $this->em->getConnection();
        $sql = "
            SELECT
                *
            FROM object
            WHERE 1
            AND (name LIKE :common_name)
            ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('common_name', str_replace(' ', '', "%$commonName%"));
        $stmt->execute();
        $resultsFound = $stmt->fetchAll();
        if (!empty($resultsFound)) {
            $objDetails = reset($resultsFound);
            $id = (int) $objDetails['id'];

            return $id; // Happy flow scenario.
        }

        return SkylistEntry::DSO_NOT_FOUND;
    }

    /**
     * @param $otherName
     *
     * @return int|string
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function findDsoByOtherName($otherName) {
        $conn = $this->em->getConnection();
        $sql = "
            SELECT
                *
            FROM object
            WHERE 1
            AND (other_names LIKE :other_names)
            ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue('other_names', str_replace(' ', '', "%$otherName%"));
        $stmt->execute();
        $resultsFound = $stmt->fetchAll();
        if (!empty($resultsFound)) {
            $objDetails = reset($resultsFound);
            $id = (int) $objDetails['id'];

            return $id; // Happy flow scenario.
        }

        return SkylistEntry::DSO_NOT_FOUND;
    }

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
}
