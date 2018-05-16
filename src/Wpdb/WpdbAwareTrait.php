<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Util\String\StringableInterface as Stringable;
use Exception as RootException;
use InvalidArgumentException;
use wpdb;

/**
 * Common functionality for objects that are aware of a WPDB instance.
 *
 * @since [*next-version*]
 */
trait WpdbAwareTrait
{
    /**
     * The WPDB instance.
     *
     * @since [*next-version*]
     *
     * @var wpdb|null
     */
    protected $wpdb;

    /**
     * Retrieves the WPDB object associated with this instance.
     *
     * @since [*next-version*]
     *
     * @return wpdb|null The WPDB instance, if any.
     */
    protected function _getWpdb()
    {
        return $this->wpdb;
    }

    /**
     * Sets the WPDB object for this instance.
     *
     * @since [*next-version*]
     *
     * @param wpdb|null $wpdb The WPDB instance, or null.
     */
    protected function _setWpdb($wpdb)
    {
        if ($wpdb !== null && !($wpdb instanceof wpdb)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not a wpdb instance or null'),
                null,
                null,
                $wpdb
            );
        }

        $this->wpdb = $wpdb;
    }

    /**
     * Creates a new invalid argument exception.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable|null $message  The error message, if any.
     * @param int|null               $code     The error code, if any.
     * @param RootException|null     $previous The inner exception for chaining, if any.
     * @param mixed|null             $argument The invalid argument, if any.
     *
     * @return InvalidArgumentException The new exception.
     */
    abstract protected function _createInvalidArgumentException(
        $message = null,
        $code = null,
        RootException $previous = null,
        $argument = null
    );

    /**
     * Translates a string, and replaces placeholders.
     *
     * @since [*next-version*]
     * @see   sprintf()
     *
     * @param string $string  The format string to translate.
     * @param array  $args    Placeholder values to replace in the string.
     * @param mixed  $context The context for translation.
     *
     * @return string The translated string.
     */
    abstract protected function __($string, $args = [], $context = null);
}
