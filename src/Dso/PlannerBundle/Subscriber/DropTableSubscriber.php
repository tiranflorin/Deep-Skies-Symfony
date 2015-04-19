<?php

namespace Dso\PlannerBundle\Subscriber;

use Doctrine\DBAL\Connection;
use Dso\PlannerBundle\Event\DropTableEvent;
use Dso\PlannerBundle\Exception\TableNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DropTableSubscriber
 *
 * @package Dso\UserBundle\EventListener
 *
 * @author  Florin Tiran  <tiran.florin@gmail.com>
 */
class DropTableSubscriber implements EventSubscriberInterface
{
    /** @var  Connection */
    private $dbConnection;

    public static function getSubscribedEvents()
    {
        return array(
            'drop.table' => array('onDropTable', 1)
        );
    }

    /**
     * @param $dbConnection
     *
     * @return DropTableSubscriber
     */
    public function setDbConnection($dbConnection)
    {
        $this->dbConnection = $dbConnection;

        return $this;
    }

    /**
     * Iterate trough a list of table names
     * and drop each table if exists.
     *
     * @throws \Dso\PlannerBundle\Exception\TableNotFoundException
     *
     * @param DropTableEvent $event
     */
    public function onDropTable(DropTableEvent $event)
    {
        $tableNames = $event->getTableNames();
        if (count($tableNames) === 0) {
            throw new TableNotFoundException('There are no table names to drop', Response::HTTP_CONFLICT);
        }

        foreach ($tableNames as $tableDetails) {
            $safeValues = array_values($tableDetails);
            $tableName = $safeValues[0];
            $this->dbConnection->executeQuery("DROP TABLE IF EXISTS `{$tableName}`");
        }
    }
}
