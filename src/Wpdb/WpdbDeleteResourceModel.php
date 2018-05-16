<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Output\TemplateInterface;
use Dhii\Util\String\StringableInterface as Stringable;
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
class WpdbDeleteResourceModel extends AbstractBaseWpdbDeleteResourceModel
{
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
        $this->_init($wpdb, $expressionTemplate, $table, $fieldColumnMap);
    }
}
