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
 * Common functionality for objects that can delete records in a database via WPDB.
 *
 * @since [*next-version*]
 */
trait DeleteCapableWpdbTrait
{
    /**
     * Executes a DELETE SQL query, deleting records in the database that satisfy the given condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null   $condition Optional condition that records must satisfy to be deleted.
     * @param OrderInterface[]|Traversable|null $ordering  The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit     The number of records to limit the query to.
     * @param int|null                          $offset    The number of records to offset by, zero-based.
     */
    protected function _delete(
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        $offset = null
    ) {
        $fieldNames = $this->_getSqlDeleteFieldNames();
        $valueHashMap = ($condition !== null)
            ? $this->_getWpdbExpressionHashMap($condition, $fieldNames)
            : [];

        $query = $this->_buildDeleteSql(
            $this->_getSqlDeleteTable(),
            $condition,
            $ordering,
            $limit,
            $offset,
            $valueHashMap
        );

        $this->_executeWpdbQuery($query, array_flip($valueHashMap));
    }

    /**
     * Builds a DELETE SQL query.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable                 $table        The name of the table to delete from.
     * @param LogicalExpressionInterface|null   $condition    The condition that records must satisfy to be deleted.
     * @param OrderInterface[]|Traversable|null $ordering     The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit        The number of records to limit the query to.
     * @param int|null                          $offset       The number of records to offset by, zero-based.
     * @param string[]|Stringable[]             $valueHashMap The mapping of term names to their hashes
     *
     * @throws InvalidArgumentException If an argument is invalid.
     * @throws OutOfRangeException      If the limit or offset are invalid numbers.
     *
     * @return string The built DELETE query.
     */
    abstract protected function _buildDeleteSql(
        $table,
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        $offset = null,
        array $valueHashMap = []
    );

    /**
     * Retrieves the SQL database table names related to this resource model.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of SQL database table names.
     */
    abstract protected function _getSqlDeleteTable();

    /**
     * Retrieves the SQL DELETE query column "field" names.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of field names.
     */
    abstract protected function _getSqlDeleteFieldNames();

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
