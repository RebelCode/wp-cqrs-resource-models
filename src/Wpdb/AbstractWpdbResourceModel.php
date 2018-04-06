<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

/**
 * Abstract functionality for WPDB resource models.
 *
 * @since [*next-version*]
 */
abstract class AbstractWpdbResourceModel
{
    /*
     * Provides functionality for executing queries and retrieving results using a WPDB instance.
     *
     * @since [*next-version*]
     */
    use GetWpdbQueryResultsCapableTrait;

    /*
     * Provides functionality for executing queries with a WPDB instance.
     *
     * @since [*next-version*]
     */
    use ExecuteWpdbQueryCapableTrait;

    /*
     * Provides awareness of and storage functionality for a WPDB instance.
     *
     * @since [*next-version*]
     */
    use WpdbAwareTrait;
}
