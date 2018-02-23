<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LiteralTermInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;

/**
 * Common functionality for objects that can generate an expression value hash map for use in PDO parameter binding.
 *
 * @since [*next-version*]
 */
trait GetWpdbExpressionHashMapCapableTrait
{
    /**
     * Retrieves the expression value hash map for a given SQL condition, for use in WPDB argument interpolation.
     *
     * @since [*next-version*]
     *
     * @param ExpressionInterface   $condition The condition instance.
     * @param string[]|Stringable[] $ignore    A list of term names to ignore, typically column names.
     *
     * @return array A map of value names to their respective hashes.
     */
    protected function _getWpdbExpressionHashMap(ExpressionInterface $condition, array $ignore = [])
    {
        $values = [];

        $this->_generateWpdbExpressionHashMap($values, $condition, $ignore);

        return $values;
    }

    /**
     * Internal recursive algorithm for generating the "hash" map for WPDB query args interpolation.
     *
     * The algorithm requires a reference to the array. This is to prevent the recursion from having to merge the
     * arrays, since the array indexes are **CRUCIAL** and array merging may change the indexes.
     *
     * The way this works is as follows: it traverses the expression recursively and for each found term, generates a
     * numbered sprintf-style placeholder, such as %4$s, which is used as the "hash". The position number needs to
     * reflect the index of the hash in the array, plus 1.
     *
     * This is because `$wpdb->prepare($query, $args)` expects the query to contain such placeholders, and so the
     * position number of a placeholders **MUST** be equal to `1` plus the index of the corresponding value in the
     * arguments array. Any SQL rendering mechanism will need to respect this scheme in order to correctly render SQL
     * queries that can be used by WPDB.
     *
     * Side note: the result is a map that maps string-casted values to their "hashes". The values may lose their
     * original type, such as an integer `5` becoming a string `"5"`. This is acceptable since the generated hash is
     * still performed on the original value and will thus be of the form `%d`. WPDB should take care to ensure that
     * the value is properly handled when preparing the query.
     *
     * @since [*next-version*]
     *
     * @param array                 $map       The map to populate with generating hashes.
     * @param ExpressionInterface   $condition The condition instance.
     * @param string[]|Stringable[] $ignore    A list of term names to ignore, typically column names.
     */
    protected function _generateWpdbExpressionHashMap(&$map, ExpressionInterface $condition, array $ignore)
    {
        foreach ($condition->getTerms() as $_idx => $_term) {
            if ($_term instanceof ExpressionInterface) {
                $this->_generateWpdbExpressionHashMap($map, $_term, $ignore);

                continue;
            }

            if (!($_term instanceof LiteralTermInterface)) {
                continue;
            }

            $_value = $_term->getValue();
            $_valueStr = $this->_normalizeString($_value);

            // If in ignore list or already in the map, ignore
            if (in_array($_valueStr, $ignore) || isset($map[$_valueStr])) {
                continue;
            }

            $map[$_valueStr] = $this->_getWpdbValueHashString($_term->getValue(), count($map) + 1);
        }
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
