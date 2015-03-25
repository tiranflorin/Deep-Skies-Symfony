<?php

namespace Dso\PlannerBundle\Services;

use Dso\PlannerBundle\Exception\QueryExecutionFailureException;
use Dso\PlannerBundle\Services\SQL\MysqlService;
use Dso\UserBundle\Entity\User;

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
        //create intermediary table:
        $this->createIntermediaryTable();

        //insert data into intermediary table:
        $this->populateIntermediaryTable();

        //create visible objects table:
        $this->createVisibleObjectsTable();

        //calculate objects visible:
        $this->populateVisibleObjectsTable();

        //delete intermediary table
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

        //TODO: validate the $lat, $long, $creation
        //TODO: expected format: $lat, long = float with three decimals

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
        WHERE dec_float > {$desiredDeclination}
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
          `ra_float`,
          `dec_float`
        FROM `{$this->intermediaryTableName}`
        ";

        try {
            $results = $this->mysqlService->executeSelectQuery($sSql);
        } catch (QueryExecutionFailureException $e) {
            echo 'Caught exception: ',  $e->getMessage(), ' File: ', $e->getFile(), "\n";
        }

        foreach ($results as $row) {
            $iRa = $row['ra_float'];
            $iDec = $row['dec_float'];
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
            `creation` TIMESTAMP DEFAULT '0000-00-00 00:00:00',
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
        $sSql = "
        CREATE TABLE IF NOT EXISTS `{$this->intermediaryTableName}`(
         `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
         `name` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `other_name` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `type` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `constellation` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `ra` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `dec` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `ra_float` varchar(30) CHARACTER SET latin1 NOT NULL,
         `dec_float` varchar(30) CHARACTER SET latin1 NOT NULL,
         `mag` decimal(3,1) NOT NULL DEFAULT '0.0',
         `subr` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `u2k` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `ti` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `size_max` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `size_min` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `pa` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `class` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `nsts` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `brstr` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `bchm` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `ngc_description` varchar(255) CHARACTER SET latin1 NOT NULL DEFAULT '',
         `notes` text CHARACTER SET latin1 NOT NULL,
         PRIMARY KEY (`id`)
        ) ENGINE=MyIsam DEFAULT CHARSET=utf8
        ";

        try {
            $this->mysqlService->executeQuery($sSql);
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
     * The CORE of the application.
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
     * @param User $user
     *
     * @return string
     */
    public function getVisibleObjectsTableName(User $user)
    {
        $tmp = new \DateTime($user->getDateTime(), new \DateTimeZone($user->getTimeZone()));
        return 'temp__custom__'. strtolower($user->getUsername()) .'_' . $user->getLatitude() . '_' . $user->getLongitude() . '_' . $tmp->format('YmdHis');;
    }
}
