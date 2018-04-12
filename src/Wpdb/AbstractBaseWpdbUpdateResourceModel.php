<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInternalExceptionCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfBoundsExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\UpdateCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use RebelCode\Storage\Resource\Sql\BuildSqlLimitCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlOrderByCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlUpdateSetCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlWhereClauseCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildUpdateSqlCapableTrait;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceCapableTrait;
use RebelCode\Storage\Resource\Sql\GetSqlColumnNameCapableContainerTrait;
use RebelCode\Storage\Resource\Sql\NormalizeSqlValueCapableTrait;
use RebelCode\Storage\Resource\Sql\RenderSqlExpressionCapableTrait;
use RebelCode\Storage\Resource\Sql\SqlExpressionTemplateAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlFieldColumnMapAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlTableAwareTrait;
use wpdb;

/**
 * Abstract base implementation of an UPDATE resource model for use with WPDB.
 *
 * This generic implementation can be configured to UPDATE records for a given table. An optional field-to-column
 * map may be provided which is used to translate consumer-friendly field names to their actual column counterpart
 * names.
 *
 * This implementation is also dependent on only a single template for rendering SQL expressions. The template instance
 * must be able to render any expression, which may be simple terms, arithmetic expressions or logical expression
 * conditions. A delegate template is recommended.
 *
 * @since [*next-version*]
 */
abstract class AbstractBaseWpdbUpdateResourceModel extends AbstractWpdbResourceModel implements UpdateCapableInterface
{
    /*
     * Provides WPDB SQL UPDATE functionality.
     *
     * @since [*next-version*]
     */
    use UpdateCapableWpdbTrait;

    /*
     * Provides SQL UPDATE query building functionality.
     *
     * @since [*next-version*]
     */
    use BuildUpdateSqlCapableTrait;

    /*
     * Provides SQL UPDATE SET building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlUpdateSetCapableTrait;

    /*
     * Provides SQL WHERE clause building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlWhereClauseCapableTrait;

    /*
     * Provides SQL ORDER BY building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlOrderByCapableTrait;

    /*
     * Provides SQL LIMIT building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlLimitCapableTrait;

    /*
     * Provides SQL reference escaping functionality.
     *
     * @since [*next-version*]
     */
    use EscapeSqlReferenceCapableTrait;

    /*
     * Provides WPDB expression value hash map generation functionality.
     *
     * @since [*next-version*]
     */
    use GetWpdbExpressionHashMapCapableTrait;

    /*
     * Provides WPDB value hash string generation functionality.
     *
     * @since [*next-version*]
     */
    use GetWpdbValueHashStringCapableTrait;

    /*
     * Provides SqL condition rendering functionality (via a template).
     *
     * @since [*next-version*]
     */
    use RenderSqlExpressionCapableTrait;

    /*
     * Provides SQL table name storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlTableAwareTrait;

    /*
     * Provides SQL field-to-column map storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlFieldColumnMapAwareTrait;

    /*
     * Provides functionality for retrieving the column name for a field name, using a container.
     *
     * @since [*next-version*]
     */
    use GetSqlColumnNameCapableContainerTrait;

    /*
     * Provides SQL expression template storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlExpressionTemplateAwareTrait;

    /*
     * Provides string normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * Provides array normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeArrayCapableTrait;

    /*
     * Provides integer normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeIntCapableTrait;

    /*
     * Provides container key normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * Provides SQL value normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeSqlValueCapableTrait;

    /*
     * Provides functionality for counting iterable variables.
     *
     * @since [*next-version*]
     */
    use CountIterableCapableTrait;

    /*
     * Provides iterator resolution functionality.
     *
     * @since [*next-version*]
     */
    use ResolveIteratorCapableTrait;

    /*
     * Provides functionality for reading from any type of container object.
     *
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * Provides functionality for creating invalid argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides functionality for creating out of range exceptions.
     *
     * @since [*next-version*]
     */
    use CreateOutOfRangeExceptionCapableTrait;

    /*
     * Provides functionality for creating out-of-bounds exceptions.
     *
     * @since [*next-version*]
     */
    use CreateOutOfBoundsExceptionCapableTrait;

    /*
     * Provides functionality for creating container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * Provides functionality for creating container not-found exceptions.
     *
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * Provides functionality for creating internal exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInternalExceptionCapableTrait;

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * Initializes the instance.
     *
     * @since [*next-version*]
     *
     * @param wpdb                  $wpdb               The WPDB instance to use to prepare and execute queries.
     * @param TemplateInterface     $expressionTemplate The template for rendering SQL expressions.
     * @param string|Stringable     $table              The name of the table for which records will be updated.
     * @param string[]|Stringable[] $fieldColumnMap     A map of field names to table column names.
     */
    protected function _init(wpdb $wpdb, TemplateInterface $expressionTemplate, $table, $fieldColumnMap)
    {
        $this->_setWpdb($wpdb);
        $this->_setSqlExpressionTemplate($expressionTemplate);
        $this->_setSqlTable($table);
        $this->_setSqlFieldColumnMap($fieldColumnMap);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function update($changeSet, LogicalExpressionInterface $condition = null, $ordering = null, $limit = null)
    {
        $this->_update($changeSet, $condition, $ordering, $limit);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlUpdateTable()
    {
        return $this->_getSqlTable();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlUpdateFieldColumnMap()
    {
        return $this->_getSqlFieldColumnMap();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _renderSqlCondition(LogicalExpressionInterface $condition, array $valueHashMap = [])
    {
        return $this->_renderSqlExpression($condition, $valueHashMap);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getTemplateForSqlExpression(TermInterface $expression)
    {
        return $this->_getSqlExpressionTemplate();
    }
}
