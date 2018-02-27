<?php

namespace RebelCode\Storage\Resource\WordPress\Posts;

use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\Invocation\Exception\InvocationExceptionInterface;
use InvalidArgumentException;
use stdClass;
use Traversable;

/**
 * Provides functionality for searching an expression's terms.
 *
 * @since [*next-version*]
 */
trait SearchExpressionTermsCapableTrait
{
    /**
     * Searches an expression's terms using a callback for matching terms.
     *
     * @since  [*next-version*]
     *
     * @param ExpressionInterface $expression The expression to search.
     * @param callable            $callback   A callback that accepts the term and expression as arguments and returns
     *                                        `true` if the term is valid and `false` if not.
     * @param int|null            $count      The maximum number of terms to search for. Searching will stop once this
     *                                        number of terms are found.
     *
     * @return TermInterface[] The list of matching terms.
     *
     * @throws InvalidArgumentException If the
     * @throws InvocationExceptionInterface If a problem occurred while invoking the callback.
     */
    protected function _searchExpressionTerms(ExpressionInterface $expression, callable $callback, $count = null)
    {
        $this->_mapIterable($expression->getTerms(), $callback, null, $count, $results);

        return $results;
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
     * @throws InvalidArgumentException     If the iterable, the callback, start, or count are invalid.
     * @throws InvocationExceptionInterface If problem during invocation of the callback.
     */
    abstract protected function _mapIterable(
        $iterable,
        $callback,
        $start = null,
        $count = null,
        array &$results = null
    );
}
