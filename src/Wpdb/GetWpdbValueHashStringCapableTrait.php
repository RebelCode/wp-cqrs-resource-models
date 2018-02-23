<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Common functionality for objects that can
 *
 * @since [*next-version*]
 */
trait GetWpdbValueHashStringCapableTrait
{
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
    protected function _getWpdbValueHashString($value, $position)
    {
        $type = gettype($value);

        switch ($type) {
            case 'integer':
                $format = 'd';
                break;

            case 'double':
                $format = 'f';
                break;

            default:
                // this will throw if not a valid string or stringable value
                $this->_normalizeString($value);
                $format = 's';
                break;
        }

        return '%' . $position . '$' . $format;
    }

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
}
