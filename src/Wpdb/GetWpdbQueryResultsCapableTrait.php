<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
use Traversable;
use wpdb;

/**
 * Common functionality for objects that can execute queries using wpdb.
 *
 * @since [*next-version*]
 */
trait GetWpdbQueryResultsCapableTrait
{
    /**
     * Executes a query using wpdb.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $query     The query to execute.
     * @param array             $inputArgs An array of arguments to use for interpolating placeholders in the query.
     *
     * @return array|Traversable The resulting records.
     */
    protected function _getWpdbQueryResults($query, array $inputArgs = [])
    {
        $this->_executeWpdbQuery($query, $inputArgs);

        return $this->_getWpdb()->last_result;
    }

    /**
     * Retrieves the wpdb object associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return wpdb The wpdb instance.
     */
    abstract protected function _getWpdb();

    /**
     * Executes a query using wpdb.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $query     The query to execute.
     * @param array             $inputArgs An array of arguments to use for interpolating placeholders in the query.
     *
     * @return int|false The number of affected records, or false on failure.
     */
    abstract protected function _executeWpdbQuery($query, array $inputArgs = []);
}
