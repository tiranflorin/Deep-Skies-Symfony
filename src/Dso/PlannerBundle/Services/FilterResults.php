<?php

namespace Dso\PlannerBundle\Services;

use Dso\PlannerBundle\Services\SQL\MysqlService;
use Knp\Component\Pager\Paginator;

class FilterResults
{
    /** @var MysqlService */
    protected $mysqlService;
    /** @var  Paginator */
    public $paginator;
    public $resultsPerPage;
    public $pageLimit;

    protected $baseTable;
    protected $visibleObjectsTable;
    protected $imagePathsTable;

    public $filterType;
    public $predefinedFilter;

    public $constellation;
    public $objectType;
    public $magnitudeMin;
    public $magnitudeMax;

    public function __construct($mysqlService, $paginator)
    {
        $this->mysqlService = $mysqlService;
        $this->paginator = $paginator;
        $this->resultsPerPage = 10; //TODO: make this configurable
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

    public function retrieveFilteredData($page = 1)
    {
        $this->pageLimit = $page;
        if($this->filterType == 'predefined') {
            switch ($this->predefinedFilter) {
                case 'naked_eye':
//                    return $this->retrieveResultsBase(' AND `source`.`mag` BETWEEN 0 AND 6.9 ', ' AND 1 ', ' AND 1 ');
                    return $this->retrieveResultsBase(0, 6.9);
                    break;
                case 'binoculars':
                    return $this->retrieveResultsBase(4, 8.5);
                    break;
                case 'small_telescope':
                    return $this->retrieveResultsBase(8.6, 12);
                    break;
            }
        }

        if($this->filterType == 'custom') {
//            $cond1 = ' AND `source`.`mag` BETWEEN ' . $this->magnitudeMin . ' AND ' . $this->magnitudeMax;
//            $cond2 = ' AND `source`.`constellation` = \'' . $this->constellation . '\'';
//            $cond3 = ' AND `source`.`type` = \'' . $this->objectType . '\'';

            return $this->retrieveResultsBase($this->magnitudeMin, $this->magnitudeMax, $this->constellation, $this->objectType);
        }
    }

    /**
     * Retrieve the paginated visible objects for current settings.
     *
     * Using 0 as a default value for constellation and type seems odd,
     * but it allows query execution to work even if the constellation
     * and type filters are not explicitly provided.
     *
     * @param string|int $minMag
     * @param string|int $maxMag
     * @param string|int $constellation
     * @param string|int $type
     *
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function retrieveResultsBase($minMag, $maxMag, $constellation = 0, $type = 0)
    {
        $sql = '
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
            AND `altitude` > 10
            AND `source`.`mag` >= ?
            AND `source`.`mag` <= ?
            AND `source`.`constellation` IN (?)
            AND `source`.`type` IN (?)
        ORDER BY
            `ObjMagnitude`';
        $stmt = $this->mysqlService->getConn()->prepare($sql);
        $stmt->bindValue(1, $minMag);
        $stmt->bindValue(2, $maxMag);

        // Support both integer and string search values for constellation and type.
        $bindConstellation = is_int($constellation) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
        $bindType = is_int($type) ? \PDO::PARAM_INT : \PDO::PARAM_STR;
        $stmt->bindValue(3, $constellation, $bindConstellation);
        $stmt->bindValue(4, $type, $bindType);

        $stmt->execute();

        $paginatedResults = $this->paginator->paginate(
            $stmt->fetchAll(),
            $this->pageLimit,
            $this->resultsPerPage
        );

        return $paginatedResults;
    }
}
