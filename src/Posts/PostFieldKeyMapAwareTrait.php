<?php

namespace RebelCode\Storage\Resource\WordPress\Posts;

use Dhii\Invocation\Exception\InvocationExceptionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use OutOfRangeException;
use stdClass;
use Traversable;

/**
 * Common functionality for objects that are aware of a post field-to-key map.
 *
 * @since [*next-version*]
 */
trait PostFieldKeyMapAwareTrait
{
    /**
     * The post field-to-key map.
     *
     * @since [*next-version*]
     *
     * @var string[]|Stringable[]
     */
    protected $postFieldKeyMap;

    /**
     * Retrieves the post field-to-key map associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return string[]|Stringable[]
     */
    protected function _getPostFieldKeyMap()
    {
        return $this->postFieldKeyMap;
    }

    /**
     * Sets the post field-to-key map for this instance.
     *
     * @since [*next-version*]
     *
     * @param string[]|Stringable[]|stdClass|Traversable $postFieldKeyMap The post field-to-key map.
     *
     * @throws InvalidArgumentException If the argument is not an array or iterable.
     * @throws OutOfRangeException If the argument contains an invalid key or invalid value.
     */
    protected function _setPostFieldKeyMap($postFieldKeyMap)
    {
        try {
            $this->_mapIterable($postFieldKeyMap, [$this, '_validatePostFieldKeyMapping'], null, null, $map);
            $this->postFieldKeyMap = $map;
        } catch (InvalidArgumentException $argumentException) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a valid iterable of strings or stringable objects'),
                null,
                null,
                $postFieldKeyMap
            );
        }
    }

    /**
     * Validates a single post key-field mapping.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $value The value.
     * @param string            $key   The key.
     *
     * @return string|Stringable The value.
     *
     * @throws OutOfRangeException If either the key or value are invalid.
     */
    protected function _validatePostKeyFieldMapping($value, $key)
    {
        if (!is_string($key)) {
            throw $this->_createOutOfRangeException(
                $this->__('Key must be a string'),
                null,
                null,
                $key
            );
        }

        if (!is_string($value) && !($value instanceof Stringable)) {
            throw $this->_createOutOfRangeException(
                $this->__('Value is not a string or stringable object'),
                null,
                null,
                $value
            );
        }

        return $value;
    }

    /**
     * Invokes a callback for each element of the iterable.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable            $iterable The iterable to map.
     * @param callable                              $callback The callback to apply to the elements of the iterable.
     *                                                        The callback return value will be stored in `$results`.
     *                                                        Signature:
     *                                                        `function ($value, $key, $iterable)`
     * @param Stringable|string|int|float|bool|null $start    The offset of the iteration, at which to start applying
     *                                                        the callback. Iterations will still happen on all
     *                                                        previous elements, but the callback will not be applied.
     *                                                        Default: 0.
     * @param Stringable|string|int|float|bool|null $count    The number  of invocations to make. Iteration will stop
     *                                                        when this number is reached. Pass 0 (zero) to iterate
     *                                                        until end. Default: 0.
     * @param array|null                            $results  If array, this will be filled with the results of the
     *                                                        callback, in the same order, preserving keys.
     *
     * @throws InvalidArgumentException     If the iterable, the callback, start, or end are invalid.
     * @throws InvocationExceptionInterface If problem during invocation of the callback.
     */
    abstract protected function _mapIterable(
        $iterable,
        $callback,
        $start = null,
        $count = null,
        array &$results = null
    );

    /**
     * Creates a new invalid argument exception.
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
     * Creates a new Out Of Range exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return OutOfRangeException The new exception.
     */
    abstract protected function _createOutOfRangeException(
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
