<?php

namespace RebelCode\Storage\Resource\WordPress\Posts;

use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use Exception as RootException;

/**
 * Common functionality for objects that are aware of a post meta data field key.`.
 *
 * @since [*next-version*]1
 */
trait PostMetaFieldKeyAwareTrait
{
    /**
     * The key for post meta data in post data arrays.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable|null
     */
    protected $postMetaFieldKey;

    /**
     * Retrieves the post meta field key associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable|null The post meta field key, if any.
     */
    protected function _getPostMetaFieldKey()
    {
        return $this->postMetaFieldKey;
    }

    /**
     * Sets the post meta field key for this instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $postMetaFieldKey The post meta field key, or null.
     *
     * @throws InvalidArgumentException If the argument is not a string or stringable instance.
     */
    protected function _setPostMetaFieldKey($postMetaFieldKey)
    {
        if ($postMetaFieldKey !== null && !is_string($postMetaFieldKey) && !($postMetaFieldKey instanceof Stringable)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a string, stringable instance or null'),
                null,
                null,
                $postMetaFieldKey
            );
        }

        $this->postMetaFieldKey = $postMetaFieldKey;
    }

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
