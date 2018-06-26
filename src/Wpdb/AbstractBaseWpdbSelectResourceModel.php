<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use ArrayObject;
use Dhii\Collection\MapFactoryInterface;
use Dhii\Collection\MapInterface;
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
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use RebelCode\Storage\Resource\Sql\BuildSelectSqlCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlColumnListCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlFromCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlGroupByClauseCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlJoinsCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlLimitCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlOffsetCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlOrderByCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlWhereClauseCapableTrait;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceCapableTrait;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceListCapableTrait;
use RebelCode\Storage\Resource\Sql\GetSqlColumnNameCapableContainerTrait;
use RebelCode\Storage\Resource\Sql\RenderSqlExpressionCapableTrait;
use RebelCode\Storage\Resource\Sql\SqlExpressionTemplateAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlFieldColumnMapAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlFieldNamesAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlJoinConditionsAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlTableListAwareTrait;
use stdClass;
use Traversable;
use wpdb;

/**
 * Abstract base implementation of a SELECT resource model for use with WPDB.
 *
 * This generic implementation can be configured to SELECT from any number of tables and with any number of JOIN
 * conditions. An optional field-to-column map may be provided which is used to translate consumer-friendly field names
 * to their actual column counterpart names.
 *
 * This implementation is also dependent on only a single template for rendering SQL expressions. The template instance
 * must be able to render any expression, which may be simple terms, arithmetic expressions or logical expression
 * conditions. A delegate template is recommended.
 *
 * @since [*next-version*]
 */
abstract class AbstractBaseWpdbSelectResourceModel extends AbstractWpdbResourceModel implements SelectCapableInterface
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
     * Provides SQL column list building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlColumnListCapableTrait;

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
     * Provides SQL GROUP BY building functionality.
     *
     * @since [*next-version*]
     */
    use BuildSqlGroupByClauseCapableTrait;

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
     * The map factory, used for creating record data maps.
     *
     * @since [*next-version*]
     *
     * @var MapFactoryInterface
     */
    protected $mapFactory;

    /**
     * Initializes the instance.
     *
     * @since [*next-version*]
     *
     * @param wpdb                         $wpdb               The WPDB instance to use to prepare and execute queries.
     * @param TemplateInterface            $expressionTemplate The template for rendering SQL expressions.
     * @param MapFactoryInterface          $factory            The factory that creates maps, for the returned records.
     * @param array|stdClass|Traversable   $tables             The tables names (values) mapping to their aliases (keys)
     *                                                         or null for no aliasing.
     * @param string[]|Stringable[]        $fieldColumnMap     A map of field names to table column names.
     * @param LogicalExpressionInterface[] $joins              A list of JOIN expressions to use in SELECT queries.
     */
    protected function _init(
        wpdb $wpdb,
        TemplateInterface $expressionTemplate,
        MapFactoryInterface $factory,
        $tables,
        $fieldColumnMap,
        $joins = []
    ) {
        $this->_setWpdb($wpdb);
        $this->_setSqlExpressionTemplate($expressionTemplate);
        $this->_setMapFactory($factory);
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
        $rawResults = $this->_select($condition, $ordering, $limit, $offset);

        return $this->_createResultSet($rawResults);
    }

    /**
     * Retrieves the map factory.
     *
     * @since [*next-version*]
     *
     * @return MapFactoryInterface The map factory instance.
     */
    protected function _getMapFactory()
    {
        return $this->mapFactory;
    }

    /**
     * Sets the map factory.
     *
     * @since [*next-version*]
     *
     * @param MapFactoryInterface $mapFactory The map factory instance.
     */
    protected function _setMapFactory(MapFactoryInterface $mapFactory)
    {
        $this->mapFactory = $mapFactory;
    }

    /**
     * Creates the result set from raw record data sets.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $rawResults The list of raw record data sets.
     *
     * @return MapInterface[]|stdClass|Traversable A list of maps, each containing data for a record.
     */
    protected function _createResultSet($rawResults)
    {
        $results = [];

        foreach ($rawResults as $_rawResult) {
            $results[] = $this->_createResult($_rawResult);
        }

        return $results;
    }

    /**
     * Creates the result map from the raw data of a record.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayObject $rawResult The raw data for the record.
     *
     * @return MapInterface The created data map that contains the record data.
     */
    protected function _createResult($rawResult)
    {
        return $this->_getMapFactory()->make([
            MapFactoryInterface::K_DATA => $rawResult
        ]);
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
        return $this->_getSqlFieldColumnMap();
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
    protected function _getSqlSelectGrouping()
    {
        return [];
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
