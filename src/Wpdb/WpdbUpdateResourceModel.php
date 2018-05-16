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
 * Concrete implementation of an UPDATE resource model for use with WPDB.
 *
 * This generic implementation can be instantiated to UPDATE records for a given table. An optional field-to-column
 * map may be provided which is used to translate consumer-friendly field names to their actual column counterpart
 * names.
 *
 * This implementation is also dependent on only a single template for rendering SQL expressions. The template instance
 * must be able to render any expression, which may be simple terms, arithmetic expressions or logical expression
 * conditions. A delegate template is recommended.
 *
 * @since [*next-version*]
 */
class WpdbUpdateResourceModel extends AbstractBaseWpdbUpdateResourceModel
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param wpdb                  $wpdb               The WPDB instance to use to prepare and execute queries.
     * @param TemplateInterface     $expressionTemplate The template for rendering SQL expressions.
     * @param string|Stringable     $table              The name of the table for which records will be updated.
     * @param string[]|Stringable[] $fieldColumnMap     A map of field names to table column names.
     */
    public function __construct(wpdb $wpdb, TemplateInterface $expressionTemplate, $table, $fieldColumnMap)
    {
        $this->_init($wpdb, $expressionTemplate, $table, $fieldColumnMap);
    }
}
