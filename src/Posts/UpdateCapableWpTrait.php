<?php

namespace RebelCode\Storage\Resource\WordPress\Posts;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Traversable;
use WP_Error;

/**
 * Common functionality for objects that can update posts in the WordPress database.
 *
 * @since [*next-version*]
 */
trait UpdateCapableWpTrait
{
    /**
     * Updates posts in the WordPress database.
     *
     * Due to limitations of the WordPress posts DB API, the condition for this method is not allowed to be negated
     * and must be a hierarchy of expressions of types "or", "equal_to", "between" or "in". The terms of the
     * expressions are also limited to post IDs.
     *
     * @since [*next-version*]
     *
     * @param array|TermInterface[]|Traversable $changeSet A map of post/meta field names mapping to the values, or
     *                                                     {@see LiteralTermInterface} instances.
     * @param LogicalExpressionInterface|null   $condition Optional condition for post IDs.
     */
    protected function _update($changeSet, LogicalExpressionInterface $condition = null)
    {
        if ($condition === null) {
            throw $this->_createInvalidArgumentException(
                $this->__('Null conditions are not supported for WordPress post updates'),
                null,
                null,
                null
            );
        }

        if ($condition->isNegated()) {
            throw $this->_createInvalidArgumentException(
                $this->__('Negated conditions are not supported for WordPress post updates'),
                null,
                null,
                null
            );
        }

        $postIds = $this->_extractPostIdsFromExpression($condition);

        if (empty($postIds)) {
            throw $this->_createInvalidArgumentException(
                $this->__('No post IDs were found in the given condition - cannot update all WordPress posts'),
                null,
                null,
                null
            );
        }

        $postData = $this->_normalizeWpPostDataArray($changeSet);
        $postIdField = $this->_getPostIdFieldName();

        foreach ($postIds as $_postId) {
            $postData[$postIdField] = $_postId;

            $this->_wpUpdatePost($postData);
        }
    }

    /**
     * Retrieves the post data as an array for use in WordPress' insertion function.
     *
     * @since [*next-version*]
     *
     * @param array|TermInterface[]|Traversable $postData The post data to normalize. If terms are given, they must be
     *                                                    {@see LiteralTermInterface} instances.
     *
     * @return array The prepared post data.
     */
    abstract protected function _normalizeWpPostDataArray($postData);

    /**
     * Extracts post IDs from a logical expression.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $expression The expression to extract from.
     *
     * @return string[]|Stringable A list of post IDs.
     *
     * @throws InvalidArgumentException If the expression is
     */
    abstract protected function _extractPostIdsFromExpression(LogicalExpressionInterface $expression);

    /**
     * Retrieves the field name used in expressions for post ID terms.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The post ID field name.
     */
    abstract protected function _getPostIdFieldName();

    /**
     * Wrapper method for the native WordPress post update function.
     *
     * @since [*next-version*]
     *
     * @param array $post The post data array, as documented
     *                    {@link https://developer.wordpress.org/reference/functions/wp_update_post/ here}.
     *
     * @return int|WP_Error The inserted ID on success, a zero of a WP_Error instance on failure.
     */
    abstract protected function _wpUpdatePost(array $post);

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
