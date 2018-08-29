<?php

namespace Dso\PlannerBundle\Services;

use Dso\PlannerBundle\Exception\QueryExecutionFailureException;
use Dso\PlannerBundle\Services\SQL\MysqlService;
use Dso\UserBundle\Entity\ObservingSite;

/**
 * Service class that handles the details of visible objects for an observer
 * with a specified latitude, longitude and timeframe
 *
 * Class CreateVisibleObjectsTable
 *
 * @package Dso\PlannerBundle\Services
 */
class CreateVisibleObjectsTable
{
    /** @var MysqlService */
    protected $mysqlService;

    /** @var  string Latitude of the observer */
    protected $lat;

    /** @var  string Longitude of the observer */
    protected $long;

    /** @var  string creation  - the date time chose by the observer */
    protected $creation;

    /** @var  string the name of the table we will use */
    protected $visibleObjectsTable;

    /** @var  string the name of the intermediary table */
    protected $intermediaryTableName;

    /**
     * Default constructor
     *
     * @param \Dso\PlannerBundle\Services\SQL\MySqlService $mysqlService
     */
    public function __construct($mysqlService)
    {
        $this->mysqlService = $mysqlService;
        $this->intermediaryTableName = 'object_intermediary';
    }

    /**
     * This method executes all the steps necessary to create a visible objects table
     * Save execution time: limit the nb of objects for which we'll calculate alt-az
     * coordinates using an intermediary table
     *
     * @return array
     */
    public function executeFlow()
    {
        $this->createIntermediaryTable();
        $this->populateIntermediaryTable();
        $this->createVisibleObjectsTable();
        $this->populateVisibleObjectsTable();
        $this->deleteIntermediaryTable();

        $result = array(
            'status' => 'Execution completed successfully',
            'table_name' => $this->visibleObjectsTable
        );

        return $result;
    }

    /**
     * @param string $username
     * @param string $lat
     * @param string $long
     * @param string $creation
     */
    public function setConfigurationDetails($username, $lat, $long, $creation)
    {
        $this->lat = $lat;
        $this->long = $long;
        $this->creation = $creation;
        $tmp = new \DateTime($creation);
        $creationDateTime = $tmp->format('YmdHis');
        $username = strtolower($username);
        $username = str_replace(' ', '', $username);

        //TODO: validate the $lat, $long, $creation
        // Expected format: $lat, long = float with three decimals.

        //if we are in the case to build the default table
        if ($this->lat == '45.230' && $this->long == '23.450') {
            $this->visibleObjectsTable = 'temp__default_45230_23450_' . $creationDateTime;
        } else {
            $this->visibleObjectsTable = 'temp__custom__'. strtolower($username) .'_' . $this->lat . '_' . $this->long . '_' . $creationDateTime;
        }
    }

    /**
     * Insert data into the intermediary table
     */
    protected function populateIntermediaryTable()
    {
        //eliminate circumpolar south objects:
        $desiredDeclination = - $this->lat;
        $sSql = "
        INSERT INTO `{$this->intermediaryTableName}`
        SELECT
          *
        FROM `object`
        WHERE decdeg > {$desiredDeclination}
        AND mag <= 15.1 OR mag >= 20
        ";

        try {
            $this->mysqlService->executeQuery($sSql);
        } catch (QueryExecutionFailureException $e) {
            echo 'Caught exception: ',  $e->getMessage(), ' File: ', $e->getFile(), "\n";
        }
    }

    /**
     * Insert data into the main table with visible objects and alt-azimuthal coordinates
     */
    protected function populateVisibleObjectsTable()
    {
        /*
        TODO
        speed improvement:
        for the second step, add the final name of the table as argument of the
        _populateTable. Ex: _populateTable(..., visibleObjectsName = null)
         if (visibleObjectsName !== null){
              $tableToJoinTo = $visibleObjectsName;
         }
         next, add a join to the select below, in order to exclude objects already present in
        visibleObjectsName table.
        */

        $sSql = "
        SELECT
          `id`,
          `rahour`,
          `decdeg`
        FROM `{$this->intermediaryTableName}`
        ";

        try {
            $results = $this->mysqlService->executeSelectQuery($sSql);
        } catch (QueryExecutionFailureException $e) {
            echo 'Caught exception: ',  $e->getMessage(), ' File: ', $e->getFile(), "\n";
        }

        foreach ($results as $row) {
            $iRa = $row['rahour'];
            $iDec = $row['decdeg'];
            $aObjectInfo = $this->getObjectHorizontalCoordinates($iRa, $iDec, $this->lat, $this->long, $this->creation);

            $objId = $row['id'];
            $objAzimuth = $aObjectInfo['azimuth'];
            $objAltitude = $aObjectInfo['altitude'];

            //if objects altitude is smaller than 5, don't save it
            //for the second pass,  objects can rise(if enough time has passed), altitude increases and are saved.
            if ($objAltitude > 5) {
                $sSql = "
                INSERT IGNORE INTO `{$this->visibleObjectsTable}`(
                `object_id`,
                `lat`,
                `long`,
                `altitude`,
                `azimuth`,
                `creation`
                )
                VALUES(
                '{$objId}',
                '{$this->lat}',
                '{$this->long}',
                '{$objAltitude}',
                '{$objAzimuth}',
                '{$this->creation}'
                )
                ";
                try {
                    $this->mysqlService->executeQuery($sSql);
                } catch (QueryExecutionFailureException $e) {
                    echo 'Caught exception: ',  $e->getMessage(), ' File: ', $e->getFile(), "\n";
                }
            }
        }
    }

    /**
     * Create temporary table for visible objects
     */
    protected function createVisibleObjectsTable()
    {
        $sSql = "
            CREATE TABLE IF NOT EXISTS `{$this->visibleObjectsTable}`(
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `object_id` int(10) unsigned NOT NULL DEFAULT 0,
            `lat` varchar(255) NOT NULL DEFAULT '',
            `long` varchar(255) NOT NULL DEFAULT '',
            `altitude` varchar(255) NOT NULL DEFAULT '',
            `azimuth` varchar(255) NOT NULL DEFAULT '',
            `creation` TIMESTAMP DEFAULT NOW(),
            PRIMARY KEY (`id`,`object_id`)) ENGINE=MyIsam AUTO_INCREMENT=1 DEFAULT CHARSET=utf8
        ";

       try {
           $this->mysqlService->executeQuery($sSql);
       } catch (QueryExecutionFailureException $e) {
           echo 'Caught exception: ',  $e->getMessage(), ' File: ', $e->getFile(), "\n";
       }
    }

    /**
     * Create intermediary table
     */
    protected function createIntermediaryTable()
    {
        $dropSql = "DROP TABLE IF EXISTS `{$this->intermediaryTableName}`";
        $createSql = "
        CREATE TABLE IF NOT EXISTS `{$this->intermediaryTableName}`(
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `rahour` float DEFAULT NULL,
          `decdeg` float DEFAULT NULL,
          `type` varchar(20) DEFAULT NULL,
          `const` varchar(20) DEFAULT NULL,
          `mag` float DEFAULT NULL,
          `name` text,
          `rarad` float DEFAULT NULL,
          `decrad` float DEFAULT NULL,
          `databaseid` int(11) DEFAULT NULL,
          `semimajor` float DEFAULT NULL,
          `semiminor` float DEFAULT NULL,
          `semimajorangle` float DEFAULT NULL,
          `object_source` int(11) DEFAULT NULL,
          `id1` varchar(25) DEFAULT NULL,
          `cat1` varchar(25) DEFAULT NULL,
          `id2` varchar(25) DEFAULT NULL,
          `cat2` varchar(25) DEFAULT NULL,
          `dupid` varchar(25) DEFAULT NULL,
          `dupcat` varchar(25) DEFAULT NULL,
          `display_mag` float DEFAULT NULL,
          `other_names` text,
          `nice_foto_target_at_focals` tinytext,
          `id3` int(11) DEFAULT NULL,
          `cat3` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
          `arp_comment` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
          `arp_category` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
          `description_nosearch` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
          `searchable_but_dontdisplay` varchar(200) CHARACTER SET utf8mb4 NOT NULL DEFAULT '',
          `id4` int(11) NOT NULL DEFAULT '0',
          `cat4` varchar(10) NOT NULL DEFAULT '',
          `id5` int(11) DEFAULT '0',
          `cat5` varchar(10) NOT NULL DEFAULT '',
          `boring_target` int(11) NOT NULL DEFAULT '0',
          `deleted` int(11) NOT NULL DEFAULT '0',
          `notes` varchar(300) DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
        ";

        try {
            $this->mysqlService->executeQuery($dropSql);
            $this->mysqlService->executeQuery($createSql);
        } catch (QueryExecutionFailureException $e) {
            echo 'Caught exception: ',  $e->getMessage(), ' File: ', $e->getFile(), "\n";
        }
    }

    /**
     * Clean up after the main table has been populated,
     * intermediary table is no longer needed
     */
    protected function deleteIntermediaryTable()
    {
        $sSql = "DROP TABLE IF EXISTS `$this->intermediaryTableName`";
        try {
            $this->mysqlService->executeQuery($sSql);
        } catch (QueryExecutionFailureException $e) {
            echo 'Caught exception: ',  $e->getMessage(), ' File: ', $e->getFile(), "\n";
        }
    }

    /**
     * The CORE of the Planner application.
     * This method calculates the alt-azimuthal coordinates of an object
     *
     * @param string $rightAscension
     * @param string $declination
     * @param string $latitude
     * @param string $long
     * @param string $date
     *
     * @return array
     */
    protected function getObjectHorizontalCoordinates($rightAscension, $declination, $latitude, $long, $date)
    {
        //calculate day Offset (x number of days from J2000.0):
        $iDate = strtotime($date);
        $iJ2000Date = mktime(12, 0, 0, 1, 1, 2000);
        $dayOffset = $iDate - $iJ2000Date;
        $dayOffset = (double) $dayOffset / (60 * 60 * 24);

        //calculate LST (Local Sideral Time):
        $LST = (double) (100.46 + (0.985647 * $dayOffset) + $long + (15 * (date("H", $iDate) + (date("i", $iDate) / 60))));
        //Add or subtract multiples of 360 to bring LST in range 0 to 360 degrees:
        if ($LST < 0) {
            $LST = $LST + 360;
        } elseif ($LST > 360) {
            $LST = $LST - 360;
        }

        // Calculate HA (Hour Angle)
        $HA = ($LST - $rightAscension + 360) % 360;

        // HA, DEC, Lat to Alt, AZ
        $x = cos($HA * (pi() / 180)) * cos($declination * (pi() / 180));
        $y = sin($HA * (pi() / 180)) * cos($declination * (pi() / 180));
        $z = sin($declination * (pi() / 180));

        $xhor = $x * cos((90 - $latitude) * (pi() / 180)) - $z * sin((90 - $latitude) * (pi() / 180));
        $yhor = $y;
        $zhor = $x * sin((90 - $latitude) * (pi() / 180)) + $z * cos((90 - $latitude) * (pi() / 180));

        $az = atan2($yhor, $xhor) * (180 / pi()) + 180;
        $alt = asin($zhor) * (180 / pi());

        $aObjectName['azimuth'] = $az;
        $aObjectName['altitude'] = $alt;

        return $aObjectName;
    }

    /**
     * @param string $visibleObjectsTable
     *
     * @return CreateVisibleObjectsTable
     */
    public function setVisibleObjectsTable($visibleObjectsTable)
    {
        $this->visibleObjectsTable = $visibleObjectsTable;

        return $this;
    }

    /**
     * @return string
     */
    public function getVisibleObjectsTable()
    {
        return $this->visibleObjectsTable;
    }

    /**
     * TODO: check why this is needed? the above "getVisibleObjectsTable" should suffice.
     *
     * @param ObservingSite $site
     * @param string        $username
     *
     * @return string
     */
    public function getVisibleObjectsTableName(ObservingSite $site, $username)
    {
        $tmp = new \DateTime($site->getDateTime(), new \DateTimeZone($site->getTimeZone()));
        $username = strtolower($username);
        $username = str_replace(' ', '', $username);
        return 'temp__custom__'. strtolower($username) .'_' . $site->getLatitude() . '_' . $site->getLongitude() . '_' . $tmp->format('YmdHis');;
    }

    /**
     * @return \Dso\PlannerBundle\Services\SQL\MysqlService
     */
    public function getMysqlService()
    {
        return $this->mysqlService;
    }
}
