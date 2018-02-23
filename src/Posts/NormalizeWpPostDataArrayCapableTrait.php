<?php

namespace RebelCode\Storage\Resource\WordPress\Posts;

use ArrayAccess;
use Dhii\Expression\LiteralTermInterface;
use Dhii\Expression\TermInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that can normalize WordPress post data arrays, for use in WordPress' native post
 * CRUD functions.
 *
 * @since [*next-version*]
 */
trait NormalizeWpPostDataArrayCapableTrait
{
    /**
     * Retrieves the post data as an array for use in WordPress' insert and update functions.
     *
     * If an array or a traversable is given, any non-WP post data is treated as meta data.
     * If a non-array container type is given, only WP post data is normalized and retrieved.
     * If any {@see LiteralTermInterface} instances are encountered, their value will be used in the result.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface|TermInterface[]|Traversable $postData The post data.
     *
     * @return array The prepared post data.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from a container.
     */
    protected function _normalizeWpPostDataArray($postData)
    {
        // If an array or traversable, then meta data can be extracted. Forward to the other method
        if (is_array($postData) || $postData instanceof Traversable) {
            return $this->_normalizeWpPostDataAndMeta($postData);
        }

        $metaKey = $this->_getWpPostDataMetaFieldKey();
        $fields = $this->_getWpPostDataFieldsToKeysMap();
        $data = [
            $metaKey => []
        ];

        foreach ($fields as $_field => $_key) {
            try {
                if (!$this->_containerHas($postData, $_field)) {
                    continue;
                }
            } catch (OutOfRangeException $outOfRangeException) {
                continue;
            }

            // Get value and normalize
            $_value = $this->_containerGet($postData, $_field);
            $_value = $this->_normalizeWpPostDataValue($_value);
            // Ensure column is a string
            $_key = $this->_normalizeString($_key);
            // Add to data
            $data[$_key] = $_value;
        }

        return $data;
    }

    /**
     * Retrieves the post and meta data from a list or traversable, for use in WordPress' insert and update functions.
     *
     * @since [*next-version*]
     *
     * @param array|TermInterface[]|Traversable $postData The list or traversable of post data to normalize.
     *
     * @return array An array of normalized post data and meta data.
     */
    protected function _normalizeWpPostDataAndMeta($postData)
    {
        $metaKey = $this->_getWpPostDataMetaFieldKey();
        $fields = $this->_getWpPostDataFieldsToKeysMap();
        $data = [
             $metaKey => []
        ];

        foreach ($postData as $_field => $_value) {
            // If field is not known, treat as meta
            if (!isset($fields[$_field])) {
                // Add to meta
                $data[$metaKey][$_field] = $this->_normalizeWpPostDataValue($_value);

                continue;
            }

            // De-alias field to column
            $_column = $this->_normalizeString($fields[$_field]);
            // Add to data
            $data[$_column] = $this->_normalizeWpPostDataValue($_value);
        }

        return $data;
    }

    /**
     * Normalizes a single value in a WordPress post data set.
     *
     * @since [*next-version*]
     *
     * @param mixed|TermInterface $value The value to normalize.
     *
     * @return mixed The normalized value.
     */
    protected function _normalizeWpPostDataValue($value)
    {
        if ($value instanceof TermInterface && !($value instanceof LiteralTermInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Only literal terms are supported for native WP operations'),
                null,
                null,
                $value
            );
        }

        if (is_array($value) || $value instanceof Traversable) {
            return array_map([$this, '_normalizeWpPostDataValue'], $value);
        }

        $origValue = $value;

        if ($value instanceof LiteralTermInterface) {
            $value = $value->getValue();
        }

        try {
            if ($value instanceof Stringable) {
                $value = $this->_normalizeString($value);
            }

            if (is_scalar($value)) {
                return $value;
            }
        } catch (InvalidArgumentException $argumentException) {
            // String normalization failed.
            // Throw the exception at the end of the method
        }

        throw $this->_createInvalidArgumentException(
            $this->__('Cannot normalize value for post data array'),
            null,
            null,
            $origValue
        );
    }

    /**
     * Retrieves a value from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable              $key       The key of the value to retrieve.
     *
     * @throws InvalidArgumentException    If container is invalid.
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws NotFoundExceptionInterface  If the key was not found in the container.
     *
     * @return mixed The value mapped to the given key.
     */
    abstract protected function _containerGet($container, $key);

    /**
     * Retrieves an entry from a container or data set.
     *
     * @since [*next-version*]
     *
     * @param array|ArrayAccess|stdClass|ContainerInterface $container The container to read from.
     * @param string|int|float|bool|Stringable              $key       The key of the value to retrieve.
     *
     * @throws ContainerExceptionInterface If an error occurred while reading from the container.
     * @throws OutOfRangeException         If the container or the key is invalid.
     *
     * @return bool True if the container has an entry for the given key, false if not.
     */
    abstract protected function _containerHas($container, $key);

    /**
     * Retrieves a map of string field names corresponding to known post data keys.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[] A list of post field key strings.
     */
    abstract protected function _getWpPostDataFieldsToKeysMap();

    /**
     * Retrieves the key where meta data is found in post data arrays.
     *
     * @since [*next-version*]
     *
     * @return string The post meta key.
     */
    abstract protected function _getWpPostDataMetaFieldKey();

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
