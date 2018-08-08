<?php

namespace Dso\ObservationsLogBundle\Services;

use Dso\ObservationsLogBundle\Entity\LoggedObject;

/**
 * Processes a csv SkySafari 6 observing list entry
 *
 * @package Dso\ObservationsLogBundle\Services
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class CsvEntry extends ExternalEntry {

    /**
     * Creates a list of observed objects.
     *
     * Columns in the order expected:
     * ["Julian Date"," Target Name"," Target Common Names"," Target Catalog Numbers"," Site Name"," Site Lat"," Site Long"," Seeing"," Faintest Star"," Sky Quality"," Equipment"," Comments"]
     *
     *
     * @param array $skylistContent
     *
     * @return LoggedObject
     */
    public function createLoggedObjectFromArray($csvRowData)
    {
        $skylistObject = new LoggedObject();

        // Try to find an id using "Target catalog Numbers"
        $dsoId = $this->retrieveDsoId($csvRowData[3]);

        if ($dsoId === SkylistEntry::DSO_NOT_FOUND) {
            // Try to find an id using "Target name"
            $dsoId = $this->retrieveDsoId($csvRowData[1]);

            // Create a new entry in the system if the object hasn't been found.
            if ($dsoId === SkylistEntry::DSO_NOT_FOUND) {
                $dsoId = $this->insertNewDso($csvRowData);
            }
        }

        $skylistObject->setObjId($dsoId);
        $date = new \DateTime('now', new \DateTimeZone('UTC'));

        $val = trim(preg_replace('/\s+/', ' ', $csvRowData[0])); // Removes the extra \n
        $number = (float) $val;  // Cast the scientific notation string to float.
        $timestamp = ($number - 2440587.5) * 86400; // Convert it from Julian notation to Unix timestamp.
        $date->setTimestamp($timestamp);
        $skylistObject->setObservedAt($date);

        if (isset($csvRowData[11]) && $csvRowData[11] != 'undefined') {
            $skylistObject->setComment(trim($csvRowData[11]));
        }

        return $skylistObject;
    }

    /**
     * Retrieve a dso_id from the system (object table for now).
     *
     * @param string $item
     *
     * @return mixed
     */
    public function retrieveDsoId($item) {
        $dso = [];
        $pieces = explode(",", $item);
        foreach ($pieces as $name) {
            $dsos = $this->em->getRepository('DsoObservationsLogBundle:DeepSkyItem')
                ->findDsosByCatalogue($name);
            if (!empty($dsos)) {
                $dso = reset($dsos);
                break;
            }
        }

        if (!empty($dso)) {
            return $dso->getId();
        } else {
            // Try to search by name also
            foreach ($pieces as $name) {
                $dsos = $this->em->getRepository('DsoObservationsLogBundle:DeepSkyItem')
                    ->findDsosBySimpleName($name);
                if (!empty($dsos)) {
                    $dso = reset($dsos);
                    break;
                }
            }

            if (!empty($dso)) {
                return $dso->getId();
            }
        }

        return SkylistEntry::DSO_NOT_FOUND;
    }

    /**
     * Saves a new DSO in our system, with SkySafari item details available.
     *
     * @param $csvRowData
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    public function insertNewDso($csvRowData) {
        $conn = $this->em->getConnection();
        $sql = "INSERT INTO object (name, other_names) VALUES (:common_name, :other_names)";
        $stmt = $conn->prepare($sql);

        $commonName = $csvRowData[1];
        $otherName = $csvRowData[2];
        if ($commonName == $otherName) {
            $otherName = '';
        }

        $stmt->bindValue('common_name', str_replace(' ', '', $commonName));
        $stmt->bindValue('other_names', str_replace(' ', '', $otherName));
        $stmt->execute();

        return $conn->lastInsertId();
    }
}
