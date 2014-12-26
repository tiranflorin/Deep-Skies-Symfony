<?php

namespace Dso\PlannerBundle\Services;

use Dso\PlannerBundle\Exception\QueryExecutionFailureException;
use Dso\PlannerBundle\Services\SQL\MysqlService;

class FilterResults
{
    /** @var MysqlService */
    protected $mysqlService;
    protected $baseTable;
    protected $visibleObjectsTable;
    protected $imagePathsTable;

    public $filterType;
    public $predefinedFilter;

    public $constellation;
    public $objectType;
    public $magnitudeMin;
    public $magnitudeMax;

    public function __construct($mysqlService)
    {
        $this->mysqlService = $mysqlService;
        $this->baseTable = 'object';
        $this->imagePathsTable = 'image_paths';
    }

    public function setConfigurationDetails($visibleObjectsTable, $filterType, $specificFilters)
    {
        $this->visibleObjectsTable = $visibleObjectsTable;
        $this->filterType = $filterType;
        if($this->filterType == 'predefined') {
            $this->predefinedFilter = $specificFilters;
        }

        if($this->filterType == 'custom') {
            $this->constellation = $specificFilters['constellation'];
            $this->objectType = $specificFilters['objType'];
            $this->magnitudeMin = $specificFilters['magMin'];
            $this->magnitudeMax = $specificFilters['magMax'];
        }
    }

    public function retrieveFilteredData()
    {
        if($this->filterType == 'predefined') {
            switch ($this->predefinedFilter) {
                case 'naked_eye':
                    return $this->retrieveResultsBase(' AND `source`.`mag` BETWEEN 0 AND 6.9 ', ' AND 1 ', ' AND 1 ');
                    break;
                case 'binoculars':
                    return $this->retrieveResultsBase(' AND `source`.`mag` BETWEEN 4 AND 8.5 ', ' AND 1 ', ' AND 1 ');
                    break;
                case 'small_telescope':
                    return $this->retrieveResultsBase(' AND `source`.`mag` BETWEEN 8.6 AND 11 ', ' AND 1 ', ' AND 1 ');
                    break;
            }
        }

        if($this->filterType == 'custom') {
            $cond1 = ' AND `source`.`mag` BETWEEN ' . $this->magnitudeMin . ' AND ' . $this->magnitudeMax;
            $cond2 = ' AND `source`.`constellation` = \'' . $this->constellation . '\'';
            $cond3 = ' AND `source`.`type` = \'' . $this->objectType . '\'';

            return $this->retrieveResultsBase($cond1, $cond2, $cond3);
        }
    }

    public function retrieveResultsBase($whereCondition1, $whereCondition2, $whereCondition3)
    {
        $pageLimit  = 0;
        $resultsPerPage = 100;

        $sSql = '
        SELECT
            altaz_coord.object_id as `Object_id`,
            source.name as `Name1`,
            source.other_name as `Name2`,
            source.type as `ObjType`,
            source.constellation as `Constellation`,
            source.mag as `ObjMagnitude`,
            source.size_min as `ObjMinSize`,
            source.size_max as `ObjMaxSize`,
            source.ngc_description as `Ngc_desc`,
            source.notes as `Other_notes`,
            altaz_coord.altitude as `obj_altitude`,
            altaz_coord.azimuth as `obj_azimuth`,
            IFNULL(img.thumb, \'default_thumbnail\') as `thumb`,
            IFNULL(img.full_size, \'default_full_size\') as `full_size`
        FROM `' . $this->baseTable . '`  as source
        LEFT JOIN `' . $this->visibleObjectsTable . '` as altaz_coord
            ON altaz_coord.object_id = source.id
        LEFT JOIN `' . $this->imagePathsTable . '` as img
            ON img.object_id = source.id
        WHERE 1
            AND `altitude` > 10 '
            . $whereCondition1 . ' '
            . $whereCondition2 . ' '
            . $whereCondition3 . '
        ORDER BY
            `ObjMagnitude`
        LIMIT ' . $pageLimit . ',' . $resultsPerPage;

        try {
            $results = $this->mysqlService->executeSelectQuery($sSql);
        } catch (QueryExecutionFailureException $e) {
            echo 'Caught exception: ',  $e->getMessage(), ' File: ', $e->getFile(), "\n";
            die;
        }

        return $results;
    }
}