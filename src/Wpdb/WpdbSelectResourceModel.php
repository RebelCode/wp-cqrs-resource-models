<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Output\TemplateInterface;
use Dhii\Util\String\StringableInterface as Stringable;
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
class WpdbSelectResourceModel extends AbstractBaseWpdbSelectResourceModel
{
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
        $this->_init($wpdb, $expressionTemplate, $tables, $fieldColumnMap, $joins);
    }
}
