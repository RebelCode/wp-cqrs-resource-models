<?php

namespace RebelCode\Storage\Resource\WordPress\Posts;

use Dhii\Data\KeyAwareInterface;
use Dhii\Data\ValueAwareInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\Type\BooleanTypeInterface;
use Dhii\Expression\Type\RelationalTypeInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Storage\Resource\Sql\Expression\SqlRelationalTypeInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use Traversable;

/**
 * ExtractPostIdsFromExpressionCapableTrait
 *
 * The expression is expected to be of type OR or EQUAL_TO. OR expressions can have terms of either of these types
 * for nesting, while EQUAL_TO expressions are scanned for a KeyAwareInterface term and a ValueAwareInterface term.
 * If a found key-aware term has a key that is considered to be a post ID field name, the values of all value-aware
 * terms are yielded as the post IDs.
 *
 * @since [*next-version*]
 */
trait ExtractPostIdsFromExpressionCapableTrait
{
    /**
     * Extracts post IDs from a logical expression.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $expression The expression to extract from.
     *
     * @return string[]|Stringable A list of post IDs.
     */
    protected function _extractPostIdsFromExpression(LogicalExpressionInterface $expression)
    {
        if ($expression->isNegated()) {
            throw $this->_createInvalidArgumentException($this->__('Negated terms are not supported'), null, null);
        }

        switch ($expression->getType()) {
            case BooleanTypeInterface::T_OR:
                return $this->_extractPostIdsFromOrExpression($expression);

            case SqlRelationalTypeInterface::T_EQUAL_TO:
                return $this->_extractPostIdsFromEqualsExpression($expression);

            case SqlRelationalTypeInterface::T_BETWEEN:
                return $this->_extractPostIdsFromBetweenExpression($expression);

            case SqlRelationalTypeInterface::T_IN:
                return $this->_extractPostIdsFromInExpression($expression);
        }

        throw $this->_createInvalidArgumentException($this->__('Expression type is not supported'), null, null);
    }

    /**
     * Extracts post IDs from an OR expression.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface $expression The expression to extract from.
     *
     * @return string[] The extracted post IDs.
     */
    protected function _extractPostIdsFromOrExpression(LogicalExpressionInterface $expression)
    {
        $postIds = [];

        foreach ($expression->getTerms() as $_term) {
            if (!($_term instanceof LogicalExpressionInterface)) {
                throw $this->_createInvalidArgumentException(
                    $this->__('Expression term is not a logical expression'),
                    null,
                    null,
                    $_term
                );
            }

            $postIds = array_merge($postIds, $this->_extractPostIdsFromExpression($_term));
        }

        return array_unique($postIds);
    }

    /**
     * Extracts post IDs from an EQUAL_TO expression.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface|null $expression The expression to extract from.
     *
     * @return string[] The extracted post IDs.
     */
    protected function _extractPostIdsFromEqualsExpression(LogicalExpressionInterface $expression = null)
    {
        $postIds = [];
        $isPostIdField = false;
        $pEntity = $this->_getPostEntityName();
        $pIdField = $this->_getPostIdFieldName();

        foreach ($expression->getTerms() as $_child) {
            if ($_child instanceof EntityFieldInterface &&
                $_child->getEntity() === $pEntity &&
                $_child->getField() === $pIdField
            ) {
                $isPostIdField = true;

                continue;
            }

            if ($_child instanceof ValueAwareInterface) {
                $_value = $_child->getValue();
                $postIds[] = $this->_normalizeInt($_value);

                continue;
            }
        }

        return ($isPostIdField)
            ? $postIds
            : [];
    }

    /**
     * Extracts post IDs from a BETWEEN expression.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface $expression The expression to extract from.
     *
     * @return string[] The extracted post IDs.
     */
    protected function _extractPostIdsFromBetweenExpression(LogicalExpressionInterface $expression)
    {
        $isPostIdField = false;
        $pEntity = $this->_getPostEntityName();
        $pIdField = $this->_getPostIdFieldName();
        $bounds = [];

        foreach ($expression->getTerms() as $_child) {
            if ($_child instanceof EntityFieldInterface &&
                $_child->getEntity() === $pEntity &&
                $_child->getField() === $pIdField
            ) {
                $isPostIdField = true;

                continue;
            }

            if ($_child instanceof ValueAwareInterface) {
                $_value = $_child->getValue();
                $bounds[] = $this->_normalizeInt($_value);

                continue;
            }
        }

        if ($isPostIdField && count($bounds) === 2) {
            return range($bounds[0], $bounds[1]);
        }

        throw $this->_createInvalidArgumentException(
            $this->__('Given sql_between expression is not a valid expression for post IDs'),
            null,
            null,
            $expression
        );
    }

    /**
     * Extracts post IDs from an IN expression.
     *
     * @since [*next-version*]
     *
     * @param LogicalExpressionInterface $expression The expression to extract from.
     *
     * @return string[] The extracted post IDs.
     */
    protected function _extractPostIdsFromInExpression(LogicalExpressionInterface $expression)
    {
        $postIds = [];
        $isPostIdField = false;
        $pEntity = $this->_getPostEntityName();
        $pIdField = $this->_getPostIdFieldName();

        foreach ($expression->getTerms() as $_child) {
            if ($_child instanceof EntityFieldInterface &&
                $_child->getEntity() === $pEntity &&
                $_child->getField() === $pIdField
            ) {
                $isPostIdField = true;

                continue;
            }

            if ($_child instanceof ValueAwareInterface) {
                $_value = $_child->getValue();
                $postIds = $this->_normalizeArray($_value);

                continue;
            }
        }

        if ($isPostIdField) {
            return $postIds;
        }

        throw $this->_createInvalidArgumentException(
            $this->__('Given sql_in expression is not a valid expression for post IDs'),
            null,
            null,
            $expression
        );
    }

    /**
     * Retrieves the entity name for posts used in expressions.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The post entity name.
     */
    abstract protected function _getPostEntityName();

    /**
     * Retrieves the field name used in expressions for post ID terms.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The post ID field name.
     */
    abstract protected function _getPostIdFieldName();

    /**
     * Normalizes a value into an integer.
     *
     * The value must be a whole number, or a string representing such a number,
     * or an object representing such a string.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|float|int $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return int The normalized value.
     */
    abstract protected function _normalizeInt($value);

    /**
     * Normalizes a value into an array.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable $value The value to normalize.
     *
     * @throws InvalidArgumentException If value cannot be normalized.
     *
     * @return array The normalized value.
     */
    abstract protected function _normalizeArray($value);

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
