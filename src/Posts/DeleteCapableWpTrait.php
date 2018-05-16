<?php

namespace RebelCode\Storage\Resource\WordPress\Posts;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use WP_Error;

/**
 * Common functionality for objects that can delete posts from the WordPress database.
 *
 * @since [*next-version*]
 */
trait DeleteCapableWpTrait
{
    /**
     * Deletes the list of posts into the WordPress database.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition
     */
    protected function _delete(LogicalExpressionInterface $condition = null)
    {
        if ($condition === null) {
            throw $this->_createInvalidArgumentException(
                $this->__('Null condition is not supported for WordPress post deletions'),
                null,
                null,
                null
            );
        }

        foreach ($this->_extractPostIdsFromExpression($condition) as $_postId) {
            $this->_wpDeletePost($_postId);
        }
    }

    /**
     * Wrapper method for the native WordPress post deletion function.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $postId The post ID.
     *
     * @return int|WP_Error The inserted ID on success, a zero of a WP_Error instance on failure.
     */
    abstract protected function _wpDeletePost($postId);

    /**
     * Extracts post IDs from a logical expression.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $expression The expression to extract from.
     *
     * @return string[]|Stringable A list of post IDs.
     */
    abstract protected function _extractPostIdsFromExpression(LogicalExpressionInterface $expression);

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
