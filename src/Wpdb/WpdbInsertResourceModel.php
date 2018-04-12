<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Util\String\StringableInterface as Stringable;
use wpdb;

/**
 * Concrete implementation of an INSERT resource model for use with WPDB.
 *
 * This generic implementation can be instantiated to INSERT into any table. An optional field-to-column map may be
 * provided which is used to translate consumer-friendly field names in insertion data sets to  their actual column
 * counterpart names.
 *
 * @since [*next-version*]
 */
class WpdbInsertResourceModel extends AbstractBaseWpdbInsertResourceModel
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param wpdb                  $wpdb           The WPDB instance to use to prepare and execute queries.
     * @param string|Stringable     $table          The table to insert records into.
     * @param string[]|Stringable[] $fieldColumnMap A map of field names to table column names.
     * @param bool                  $insertBulk     True to insert records in a single bulk query, false to insert them
     *                                              in separate queries.
     */
    public function __construct(wpdb $wpdb, $table, $fieldColumnMap, $insertBulk = true)
    {
        $this->_init($wpdb, $table, $fieldColumnMap, $insertBulk);
    }
}
