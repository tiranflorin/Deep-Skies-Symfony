<?php

namespace Dso\PlannerBundle\Services;

use Doctrine\DBAL\Connection;
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
            return $this->retrieveResultsBase($this->magnitudeMin, $this->magnitudeMax, $this->constellation, $this->objectType);
        }
    }

    /**
     * Retrieve the paginated visible objects for current settings.
     *
     * Using an array with a single 0 element as a default value for constellation
     * and type seems odd, but it allows query execution to work even if the
     * constellation and type filters are not explicitly provided.
     *
     * @param string|int $minMag
     * @param string|int $maxMag
     * @param array      $constellation
     * @param array      $type
     *
     * @return \Knp\Component\Pager\Pagination\PaginationInterface
     */
    public function retrieveResultsBase($minMag, $maxMag, $constellation = array(0), $type = array(0))
    {
        $sql = "
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
            IFNULL(img.thumb, 'no_image_available.png') as `thumb`,
            IFNULL(img.full_size, 'no_image_available_large.png') as `full_size`
        FROM `{$this->baseTable}`  as source
        LEFT JOIN `{$this->visibleObjectsTable}` as altaz_coord
            ON altaz_coord.object_id = source.id
        LEFT JOIN `{$this->imagePathsTable}` as img
            ON img.object_id = source.id
        WHERE 1
            AND `altitude` > 10
            AND `source`.`mag` >= ?
            AND `source`.`mag` <= ?
            AND `source`.`constellation` IN (?)
            AND `source`.`type` IN (?)
        ORDER BY
            `ObjMagnitude`";

        // Determine the proper query type for the two parameters. Find a prettier way to decide.
        $typeConst = ($constellation[0] === 0 && count($constellation) === 1) ? Connection::PARAM_INT_ARRAY : Connection::PARAM_STR_ARRAY;
        $typeDsoObjects = ($type[0] === 0 && count($type) === 1) ? Connection::PARAM_INT_ARRAY : Connection::PARAM_STR_ARRAY;

        // Tweak the query parameters for the special case of the custom filter with all visible constellations selected.
        if (count($constellation) === 1 && $constellation[0] == 'allvisible') {
            $typeConst = Connection::PARAM_INT_ARRAY;
            $constellation[0] = 0;
        }

        // Fine tuning for searching nebula.
        if ($type[0] == 'other'){
            // Add the other available types of nebula.
            $type[] = 'brtnb';
            $type[] = 'drknb';
            $type[] = 'cl+nb';
        }

        $stmt = $this->mysqlService->getConn()->executeQuery(
            $sql,
            array(
                (int) $minMag,
                (int) $maxMag,
                $constellation,
                $type,
            ),
            array(
                \PDO::PARAM_INT,
                \PDO::PARAM_INT,
                $typeConst,
                $typeDsoObjects
            )
        );

        $paginatedResults = $this->paginator->paginate(
            $stmt->fetchAll(),
            $this->pageLimit,
            $this->resultsPerPage
        );

        return $paginatedResults;
    }

    public function retrieveSearchResults($page, $keywords)
    {
        $this->pageLimit = $page;
        $sql = "
        SELECT
            source.id as `id`,
            source.name as `Name1`,
            source.other_name as `Name2`,
            source.type as `ObjType`,
            source.constellation as `Constellation`,
            source.mag as `ObjMagnitude`,
            source.size_min as `ObjMinSize`,
            source.size_max as `ObjMaxSize`,
            source.ngc_description as `Ngc_desc`,
            source.notes as `Other_notes`,
            IFNULL(img.thumb, 'no_image_available.png') as `thumb`,
            IFNULL(img.full_size, 'no_image_available_large.png') as `full_size`
        FROM `{$this->baseTable}` as source
        LEFT JOIN `{$this->imagePathsTable}` as img
            ON img.object_id = source.id
        WHERE
        name LIKE ? OR
        other_name LIKE ? OR
        notes LIKE ?
        ORDER BY
            `mag`";

        $keyword = "%$keywords%";
        $stmt = $this->mysqlService->getConn()->executeQuery(
            $sql,
            array($keyword, $keyword, $keyword),
            array(\PDO::PARAM_STR, \PDO::PARAM_STR, \PDO::PARAM_STR)
        );

        $paginatedResults = $this->paginator->paginate(
            $stmt->fetchAll(),
            $this->pageLimit,
            $this->resultsPerPage
        );

        return $paginatedResults;
    }

    public function getSelectedFilter($selection)
    {
        switch ($selection) {
            case 'naked_eye':
                $selected = 'naked_eye';
                break;
            case 'binoculars':
                $selected = 'binoculars';
                break;
            case 'small_telescope':
                $selected = 'small_telescope';
                break;
            default:
                $selected = 'naked_eye';
        }

        return $selected;
    }

    /**
     * @param $results
     *
     * @return FilterResults
     */
    public function setResultsPerPage($results)
    {
        $this->resultsPerPage = $results;

        return $this;
    }
}
