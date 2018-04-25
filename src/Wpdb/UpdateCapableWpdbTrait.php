<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Traversable;

/**
 * Common functionality for objects that can update records in a database using WPDB.
 *
 * @since [*next-version*]
 */
trait UpdateCapableWpdbTrait
{
    /**
     * Executes an UPDATE SQL query, updating records in the database that satisfy the given condition.
     *
     * @since [*next-version*]
     *
     * @param array|TermInterface[]|Traversable $changeSet The change set, mapping field names to their new values or
     *                                                     value expressions.
     * @param LogicalExpressionInterface|null   $condition Optional condition that records must satisfy to be updated.
     * @param OrderInterface[]|Traversable|null $ordering  The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit     The number of records to limit the query to.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     */
    protected function _update(
        $changeSet,
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null
    ) {
        $fields = array_keys($this->_getSqlUpdateFieldColumnMap());
        // Hash map for the condition
        $hashValueMap = ($condition !== null)
            ? $this->_getWpdbExpressionHashMap($condition, $fields)
            : [];
        // Fields to columns in change set, and hashes for values in change set
        $changeSet = $this->_preProcessChangeSet($changeSet, $hashValueMap);

        $values = array_values($hashValueMap);
        $tokens = array_combine($values, array_fill(0, count($values), '%s'));

        $query = $this->_buildUpdateSql(
            $this->_getSqlUpdateTable(),
            $changeSet,
            $condition,
            $ordering,
            $limit,
            $tokens
        );

        $this->_executeWpdbQuery($query, $values);
    }

    /**
     * Process the change set to alias field names to column names and populate the value hash map.
     *
     * @since [*next-version*]
     *
     * @param array|TermInterface[]|Traversable $changeSet The change set, mapping field names to their new values or
     *                                                     value expressions.
     * @param array                             $hashMap   The hash-to-value map to populate with new hashes.
     *
     * @return array The processed change set.
     */
    protected function _preProcessChangeSet($changeSet, &$hashMap = [])
    {
        if ($hashMap === null) {
            $hashMap = [];
        }

        $newChangeSet = [];
        $fcMap        = $this->_getSqlUpdateFieldColumnMap();

        foreach ($changeSet as $_field => $_value) {
            // If unknown field name, skip
            if (!isset($fcMap[$_field])) {
                continue;
            }
            // Add to new change set, but keyed with column name
            $_column                = $fcMap[$_field];
            $newChangeSet[$_column] = $_value;

            // Get hash for value
            $_valueStr  = $this->_normalizeString($_value);
            $_valueHash = ($_value instanceof TermInterface)
                ? $this->_getWpdbExpressionHashMap($_value)
                : $this->_getWpdbValueHashString($_valueStr, count($hashMap) + 1);
            // Add to value hash map
            $hashMap[$_valueHash] = $_value;
        }

        return $newChangeSet;
    }

    /**
     * Hashes a query value for use in WPDB queries when argument interpolating.
     *
     * @since [*next-version*]
     *
     * @param string $value    The value to hash.
     * @param int    $position The position of the value in the hash map.
     *
     * @return string The string hash.
     */
    abstract protected function _getWpdbValueHashString($value, $position);

    /**
     * Builds a UPDATE SQL query.
     *
     * Consider using a countable argument for the $changeSet parameter for better performance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable                 $table        The name of the table to insert into.
     * @param array|TermInterface[]|Traversable $changeSet    The change set, mapping field names to their new values
     *                                                        or value expressions.
     * @param LogicalExpressionInterface|null   $condition    Optional WHERE clause condition.
     * @param OrderInterface[]|Traversable|null $ordering     The ordering, as a list of OrderInterface instances.
     * @param int|null                          $limit        The number of records to limit the query to.
     * @param array                             $valueHashMap Optional map of value names and their hashes.
     *
     * @throws InvalidArgumentException If the change set is empty.
     *
     * @return string The built UPDATE query.
     */
    abstract protected function _buildUpdateSql(
        $table,
        $changeSet,
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        array $valueHashMap = []
    );

    /**
     * Retrieves the SQL database table names related to this resource model.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of SQL database table names.
     */
    abstract protected function _getSqlUpdateTable();

    /**
     * Retrieves the fields-to-columns mapping for use in UPDATE SQL queries.
     *
     * @since [*next-version*]
     *
     * @return array A map containing the field names as keys and the matching column names as values.
     */
    abstract protected function _getSqlUpdateFieldColumnMap();

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

    /**
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param string|int|float|bool|Stringable $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);

    /**
     * Creates a new Dhii invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
