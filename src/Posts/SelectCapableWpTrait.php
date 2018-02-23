<?php

namespace RebelCode\Storage\Resource\WordPress\Posts;

use Dhii\Expression\LogicalExpressionInterface;
use InvalidArgumentException;
use Traversable;
use WP_Query;

/**
 * Common functionality for objects that can retrieve WordPress posts from a database.
 *
 * @since [*next-version*]
 */
trait SelectCapableWpTrait
{
    /**
     * Executes a `WP_Query` query, retrieving posts from the database that satisfy the given condition.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $condition Optional condition that records must satisfy.
     *                                                   If null, all records in the target table are retrieved.
     *
     * @return array|Traversable A list of retrieved posts.
     */
    protected function _select(LogicalExpressionInterface $condition = null)
    {
        $args = $this->_buildWpQueryArgs($condition);
        $args = $this->_filterWpQueryArgs($args);
        $wpQuery = $this->_createWpQuery($args);
        $posts = $wpQuery->posts;

        return $this->_filterWpQueryPosts($posts);
    }

    /**
     * Builds a given expression into array arguments that can be passed to `WP_Query`.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface $expression The expression to build into the `WP_Query` array argument.
     *
     * @throws InvalidArgumentException If the given expression could not be built.
     *
     * @return array The resulting `WP_Query` array argument.
     */
    abstract protected function _buildWpQueryArgs(LogicalExpressionInterface $expression);

    /**
     * Filters the arguments to be passed to `WP_Query` to make pre-processing changes.
     *
     * @since [*next-version*]
     *
     * @param array $args The arguments to be passed to `WP_Query`.
     *
     * @return array The filtered arguments to be passed to `WP_Query`.
     */
    abstract protected function _filterWpQueryArgs(array $args);

    /**
     * Filters the posts retrieved via `WP_Query` to make last-second post-processing changes.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $posts The posts retrieved via `WP_Query`.
     *
     * @return array|Traversable The filtered posts.
     */
    abstract protected function _filterWpQueryPosts($posts);

    /**
     * Creates a new `WP_Query` instance.
     *
     * @since [*next-version*]
     *
     * @param array $args The arguments to use for constructing the `WP_Query` instance.
     *
     * @return WP_Query
     */
    abstract protected function _createWpQuery(array $args);
}
