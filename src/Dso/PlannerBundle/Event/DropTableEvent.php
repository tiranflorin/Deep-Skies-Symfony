<?php

namespace Dso\PlannerBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class DropTableEvent
 *
 * @package Dso\PlannerBundle\Event
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class DropTableEvent extends Event
{
    const DROP_TABLE = 'drop.table';

    /**  @var array An array of table names to drop. */
    protected $tableNames;

    public function __construct($tableNames) {
        $this->tableNames = $tableNames;
    }

    /**
     * @return array
     */
    public function getTableNames()
    {
        return $this->tableNames;
    }
}
