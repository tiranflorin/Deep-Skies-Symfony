<?php

namespace Dso\PlannerBundle\Services;

use Doctrine\DBAL\Connection;
use Dso\PlannerBundle\Exception\QueryExecutionFailureException;
use Dso\PlannerBundle\Services\SQL\MysqlService;

/** Service class that handles ...
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

    /** @var  string the name of the table to be created */
    protected $tableName;

    /**
     * @param $mysqlService
     */
    public function __construct($mysqlService)
    {
        $this->mysqlService = $mysqlService;
    }

    public function setConfigurationDetails($lat, $long, $creation)
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
            $this->tableName = 'temp__default_45230_23450_' . $creationDateTime;
        } else {
            $this->tableName = 'temp__custom_' . $this->lat . '_' . $this->long . '_' . $creationDateTime;
        }
    }

    /**
     * Create temporary table with visible objects
     */
    protected function createTable()
    {
        $sSql = "
            CREATE TABLE IF NOT EXISTS `{$this->tableName}`(
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
           echo 'Caught exception: ',  $e->getMessage(), 'File: ', $e->getFile(), "\n";
       }
    }

    /**
     * The CORE of the application.
     * This method calculates the alt-azimuthal coordinates of an object
     *
     * @param $RA
     * @param $Dec
     * @param $Lat
     * @param $Long
     * @param $Date
     *
     * @return array
     */
    protected function _getObjectHorizontalCoordinates($RA, $Dec, $Lat, $Long, $Date)
    {
        //calculate day Offset (x number of days from J2000.0):
        $iDate = strtotime($Date);
        $iJ2000Date = mktime(12, 0, 0, 1, 1, 2000);
        $dayOffset = $iDate - $iJ2000Date;
        $dayOffset = (double)$dayOffset / (60 * 60 * 24);
        //var_dump(date('Y-m-d h:i:s',$iJ2000Date));
        //var_dump($dayOffset);

        //calculate LST (Local Sideral Time):
        $LST = (double)(100.46 + (0.985647 * $dayOffset) + $Long + (15 * (date("H", $iDate) + (date("i", $iDate) / 60))));
        //Add or subtract multiples of 360 to bring LST in range 0 to 360 degrees:
        if ($LST < 0) {
            $LST = $LST + 360;
        } elseif ($LST > 360) {
            $LST = $LST - 360;
        }
        //var_dump($LST);

        // Calculate HA (Hour Angle)
        $HA = ($LST - $RA + 360) % 360;
        //var_dump($HA);

        // HA, DEC, Lat to Alt, AZ
        $x = cos($HA * (pi() / 180)) * cos($Dec * (pi() / 180));
        $y = sin($HA * (pi() / 180)) * cos($Dec * (pi() / 180));
        $z = sin($Dec * (pi() / 180));

        $xhor = $x * cos((90 - $Lat) * (pi() / 180)) - $z * sin((90 - $Lat) * (pi() / 180));
        $yhor = $y;
        $zhor = $x * sin((90 - $Lat) * (pi() / 180)) + $z * cos((90 - $Lat) * (pi() / 180));

        $az = atan2($yhor, $xhor) * (180 / pi()) + 180;
        $alt = asin($zhor) * (180 / pi());

        //var_dump($az);
        //var_dump($alt);

        $aObjectName['azimuth'] = $az;
        $aObjectName['altitude'] = $alt;

        return $aObjectName;
    }


}