<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Exception\CreateInternalExceptionCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfBoundsExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\TermInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use RebelCode\Storage\Resource\Sql\BuildSelectSqlCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlFromCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlJoinsCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlLimitCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlOffsetCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlOrderByCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlWhereClauseCapableTrait;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceCapableTrait;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceListCapableTrait;
use RebelCode\Storage\Resource\Sql\GetSqlColumnNameCapableContainerTrait;
use RebelCode\Storage\Resource\Sql\RenderSqlExpressionCapableTrait;
use RebelCode\Storage\Resource\Sql\SqlColumnNamesAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlExpressionTemplateAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlFieldColumnMapAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlFieldNamesAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlJoinConditionsAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlTableListAwareTrait;
use stdClass;
use Traversable;
use wpdb;

/**
 * Concrete implementation of a SELECT resource model for use with WPDB.
 *
 * This generic implementation can be instantiated to SELECT from any number of tables and with any number of JOIN
 * conditions. An optional field-to-column map may be provided which is used to translate consumer-friendly field names
 * to their actual column counterpart names.
 *
 * This implementation is also dependent on only a single template for rendering SQL expressions. The template instance
 * must be able to render any expression, which may be simple terms, arithmetic expressions or logical expression
 * conditions. A delegate template is recommended.
 *
 * @since [*next-version*]
 */
class WpdbSelectResourceModel extends AbstractWpdbResourceModel implements SelectCapableInterface
{
    /*
     * Provides WPDB SQL SELECT functionality.
     *
     * @since [*next-version*]
     */
    use SelectCapableWpdbTrait;

    /*
     * Provides SQL SELECT query building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSelectSqlCapableTrait;

    /*
     * Provides SQL FROM querying building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlFromCapableTrait;

    /*
     * Provides SQL JOIN building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlJoinsCapableTrait;

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
     * Provides SQL OFFSET building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlOffsetCapableTrait;

    /*
     * Provides SQL reference escaping functionality.
     *
     * @since [*next-version*]
     */
    use EscapeSqlReferenceCapableTrait;

    /*
     * Provides SQL reference list escaping functionality.
     *
     * @since [*next-version*]
     */
    use EscapeSqlReferenceListCapableTrait;

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
     * Provides SQL condition rendering functionality (via a template).
     *
     * @since [*next-version*]
     */
    use RenderSqlExpressionCapableTrait;

    /*
     * Provides SQL table list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlTableListAwareTrait;

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
     * Provides SQL field name list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlFieldNamesAwareTrait;

    /*
     * Provides SQL column name list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlColumnNamesAwareTrait;

    /*
     * Provides SQL join condition list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlJoinConditionsAwareTrait;

    /*
     * Provides SQL expression template storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlExpressionTemplateAwareTrait;

    /*
     * Provides integer normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeIntCapableTrait;

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
     * Provides iterable normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeIterableCapableTrait;

    /*
     * Provides iterable counting functionality.
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
     * Provides functionality for creating out-of-range exceptions.
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
     * The JOIN mode to use.
     *
     * @since [*next-version*]
     */
    const JOIN_MODE = 'LEFT';

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param wpdb                         $wpdb               The WPDB instance to use to prepare and execute queries.
     * @param TemplateInterface            $expressionTemplate The template for rendering SQL expressions.
     * @param array|stdClass|Traversable   $tables             The tables names (values) mapping to their aliases (keys)
     *                                                         or null for no aliasing.
     * @param string[]|Stringable[]        $fieldColumnMap     A map of field names to table column names.
     * @param LogicalExpressionInterface[] $joins              A list of JOIN expressions to use in SELECT queries.
     */
    public function __construct(
        wpdb $wpdb,
        TemplateInterface $expressionTemplate,
        $tables,
        $fieldColumnMap,
        $joins = []
    ) {
        $this->_setWpdb($wpdb);
        $this->_setSqlExpressionTemplate($expressionTemplate);
        $this->_setSqlTableList($tables);
        $this->_setSqlFieldColumnMap($fieldColumnMap);
        $this->_setSqlJoinConditions($joins);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function select(
        LogicalExpressionInterface $condition = null,
        $ordering = null,
        $limit = null,
        $offset = null
    ) {
        return $this->_select($condition, $ordering, $limit, $offset);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectTables()
    {
        return $this->_getSqlTableList();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectFieldNames()
    {
        return $this->_getSqlFieldNames();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectColumns()
    {
        return $this->_getSqlColumnNames();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlSelectJoinConditions()
    {
        return $this->_getSqlJoinConditions();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlJoinType(ExpressionInterface $expression)
    {
        return 'INNER';
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

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _normalizeKey($key)
    {
        return $this->_normalizeString($key);
    }
}
