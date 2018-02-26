<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\DeleteCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use RebelCode\Storage\Resource\Sql\BuildDeleteSqlCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlWhereClauseCapableTrait;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceCapableTrait;
use RebelCode\Storage\Resource\Sql\RenderSqlExpressionCapableTrait;
use RebelCode\Storage\Resource\Sql\SqlExpressionTemplateAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlFieldColumnMapAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlFieldNamesAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlTableAwareTrait;
use wpdb;

/**
 * Concrete implementation of a DELETE resource model for use with WPDB!.
 *
 * This generic implementation can be instantiated to DELETE records for a given table. An optional field-to-column
 * map may be provided which is used to translate consumer-friendly field names to their actual column counterpart
 * names.
 *
 * This implementation is also dependent on only a single template for rendering SQL expressions. The template instance
 * must be able to render any expression, which may be simple terms, arithmetic expressions or logical expression
 * conditions. A delegate template is recommended.
 *
 * @since [*next-version*]
 */
class WpdbDeleteResourceModel extends AbstractWpdbResourceModel implements DeleteCapableInterface
{
    /*
     * Provides WPDB SQL DELETE functionality.
     *
     * @since [*next-version*]
     */
    use DeleteCapableWpdbTrait;

    /*
     * Provides SQL DELETE query building functionality.
     *
     * @since [*next-version*]
     */
    use BuildDeleteSqlCapableTrait;

    /*
     * Provides SQL WHERE clause building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlWhereClauseCapableTrait;

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
     * Provides SQL reference escaping functionality.
     *
     * @since [*next-version*]
     */
    use EscapeSqlReferenceCapableTrait;

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
     * Provides SQL field names storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlFieldNamesAwareTrait;

    /*
     * Provides SQL field-to-column map storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlFieldColumnMapAwareTrait;

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
     * Provides functionality for creating invalid argument exceptions.
     *
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param wpdb                  $wpdb               The WPDB instance to use to prepare and execute queries.
     * @param TemplateInterface     $expressionTemplate The template for rendering SQL expressions.
     * @param string|Stringable     $table              The name of the table from which records will be deleted.
     * @param string[]|Stringable[] $fieldColumnMap     A map of field names to table column names.
     */
    public function __construct(wpdb $wpdb, TemplateInterface $expressionTemplate, $table, $fieldColumnMap)
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
    public function delete(LogicalExpressionInterface $condition = null)
    {
        $this->_delete($condition);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlDeleteTable()
    {
        return $this->_getSqlTable();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlDeleteFieldNames()
    {
        return $this->_getSqlFieldNames();
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
