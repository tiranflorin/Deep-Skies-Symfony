<?php

namespace Dso\PlannerBundle\Services\SQL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Dso\PlannerBundle\Exception\QueryExecutionFailureException;

/**
 * Service class that executes mysql queries and handles their exceptions
 *
 * Class MysqlService
 *
 * @package Dso\PlannerBundle\Services\SQL
 */
class MySqlService
{
    /** @var Connection */
    protected $conn;

    /**
     * Constructor function
     *
     * @param Connection $conn
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * Database connection setter
     *
     * @param Connection $conn
     *
     * @return $this
     */
    public function setConn($conn)
    {
        $this->conn = $conn;

        return $this;
    }

    /**
     * Database Connection getter
     *
     * @return Connection
     */
    public function getConn()
    {
        return $this->conn;
    }

    /** Executes a Mysql query
     * returns the number of affected rows
     *
     * @param string $query
     *
     * @throws QueryExecutionFailureException
     *
     * @return integer
     */
    public function executeQuery($query)
    {
        try {
            $nbrRowsAffected = $this->conn->exec($query);
        } catch (DBALException $e) {
            throw new QueryExecutionFailureException('ERROR: Unable to execute query (executeQuery method of MySql service class).', 0, $e);
        }

        return $nbrRowsAffected;
    }

    /** Executes a select query and returns the results
     *
     * @param string $selectQuery
     *
     * @throws QueryExecutionFailureException
     *
     * @return array
     */
    public function executeSelectQuery($selectQuery)
    {
        try {
            $results = $this->conn->fetchAll($selectQuery);
        } catch (DBALException $e) {
            throw new QueryExecutionFailureException('ERROR: Unable to execute query (executeSelectQuery method of MySql service class).', 0, $e);
        }
        $this->conn->close();
        return $results;
    }
}
