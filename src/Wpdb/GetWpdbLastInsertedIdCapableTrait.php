<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Util\String\StringableInterface as Stringable;
use wpdb;

/**
 * Functionality for retrieving the ID of the last record that was inserted with WPDB.
 *
 * @since [*next-version*]
 */
trait GetWpdbLastInsertedIdCapableTrait
{
    /**
     * Retrieves the ID of the record that was last inserted with WPDB.
     *
     * @since [*next-version*]
     *
     * @return int|string|Stringable The last inserted ID.
     */
    protected function _getWpdbLastInsertedId()
    {
        return $this->_getWpdb()->insert_id;
    }

    /**
     * Retrieves the wpdb object associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return wpdb The wpdb instance.
     */
    abstract protected function _getWpdb();
}
