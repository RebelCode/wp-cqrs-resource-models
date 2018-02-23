<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use wpdb;

/**
 * Common functionality for objects that can execute queries using wpdb.
 *
 * @since [*next-version*]
 */
trait ExecuteWpdbQueryCapableTrait
{
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
    protected function _executeWpdbQuery($query, array $inputArgs = [])
    {
        $wpdb = $this->_getWpdb();
        $queryStr = $this->_normalizeString($query);
        $prepared = $wpdb->prepare($queryStr, $inputArgs);

        return $wpdb->query($prepared);
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
     * Normalizes a value to its string representation.
     *
     * The values that can be normalized are any scalar values, as well as
     * {@see StringableInterface).
     *
     * @since [*next-version*]
     *
     * @param Stringable|string|int|float|bool $subject The value to normalize to string.
     *
     * @throws InvalidArgumentException If the value cannot be normalized.
     *
     * @return string The string that resulted from normalization.
     */
    abstract protected function _normalizeString($subject);
}
