<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use OutOfRangeException;
use Traversable;

/**
 * Common functionality for objects that can retrieve records from a database using WPDB.
 *
 * @since [*next-version*]
 */
trait SelectCapableWpdbTrait
{
    /**
     * Executes a SELECT WPDB SQL query, retrieving records from the database that satisfy the given condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null   $condition Optional condition that records must satisfy.
     *                                                     If null, all records in the target table are retrieved.
     * @param OrderInterface[]|Traversable|null $ordering  The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit     The number of records to limit the query to.
     * @param int|null                          $offset    The number of records to offset by, zero-based.
     *
     * @return array|Traversable A list of retrieved records.
     */
    protected function _select(
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        $offset = null
    ) {
        $fields = $this->_getSqlSelectFieldNames();
        $valueHashMap = ($condition !== null)
            ? $this->_getWpdbExpressionHashMap($condition, $fields)
            : [];

        $query = $this->_buildSelectSql(
            $this->_getSqlSelectColumns(),
            $this->_getSqlSelectTables(),
            $this->_getSqlSelectJoinConditions(),
            $condition,
            $ordering,
            $limit,
            $offset,
            $valueHashMap
        );

        return $this->_executeWpdbQuery($query, array_flip($valueHashMap));
    }

    /**
     * Builds a SELECT SQL query.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[]             $columns        A list of names of columns to select.
     * @param array                             $tables         A list of names of tables to select from.
     * @param LogicalExpressionInterface[]      $joinConditions Optional list of JOIN conditions, keyed by table name.
     * @param LogicalExpressionInterface|null   $whereCondition Optional WHERE condition.
     * @param OrderInterface[]|Traversable|null $ordering       The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit          The number of records to limit the query to.
     * @param int|null                          $offset         The number of records to offset by, zero-based.
     * @param array                             $valueHashMap   Optional map of value names and their hashes.
     *
     * @throws InvalidArgumentException If an argument is invalid.
     * @throws OutOfRangeException      If the limit or offset are invalid numbers.
     *
     * @return string The built SQL query string.
     */
    abstract protected function _buildSelectSql(
        array $columns,
        array $tables,
        array $joinConditions = [],
        LogicalExpressionInterface $whereCondition = null,
        $ordering = null,
        $limit = null,
        $offset = null,
        array $valueHashMap = []
    );

    /**
     * Retrieves the SQL database table names used in SQL SELECT queries.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of SQL database table names.
     */
    abstract protected function _getSqlSelectTables();

    /**
     * Retrieves the names of the columns used in SQL SELECT queries.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of column names.
     */
    abstract protected function _getSqlSelectColumns();

    /**
     * Retrieves the SQL SELECT query column "field" names.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of field names.
     */
    abstract protected function _getSqlSelectFieldNames();

    /**
     * Retrieves the JOIN conditions used in SQL SELECT queries.
     *
     * @since [*next-version*]
     *
     * @return LogicalExpressionInterface[] An assoc. array of logical expressions, keyed by the joined table name.
     */
    abstract protected function _getSqlSelectJoinConditions();

    /**
     * Retrieves the expression value hash map for a given WPDB SQL condition, for use in WPDB args interpolation.
     *
     * @since [*next-version*]
     *
     * @param ExpressionInterface   $condition The condition instance.
     * @param string[]|Stringable[] $ignore    A list of term names to ignore, typically column names.
     *
     * @return array A map of value names to their respective hashes.
     */
    abstract protected function _getWpdbExpressionHashMap(ExpressionInterface $condition, array $ignore = []);

    /**
     * Executes a query using wpdb.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $query     The query to execute.
     * @param array             $inputArgs An array of arguments to use for interpolating placeholders in the query.
     *
     * @return array A list of associative arrays, each representing a single record.
     */
    abstract protected function _executeWpdbQuery($query, array $inputArgs = []);
}
