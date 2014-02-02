<?php

namespace Dso\PlannerBundle\Exception;

use Symfony\Component\Config\Definition\Exception\Exception;

/**
 * Exception thrown if an SQL query execution fails.
 * Use with a DBALException as previous Exception.
 *
 * Class QueryExecutionFailureException
 *
 * @package Dso\PlannerBundle\Exception
 */
class QueryExecutionFailureException extends Exception
{

} 