<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Storage\Resource\InsertCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use RebelCode\Storage\Resource\Sql\BuildInsertSqlCapableTrait;
use RebelCode\Storage\Resource\Sql\BuildSqlRecordValuesCapableTrait;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceCapableTrait;
use RebelCode\Storage\Resource\Sql\EscapeSqlReferenceListCapableTrait;
use RebelCode\Storage\Resource\Sql\NormalizeSqlValueCapableTrait;
use RebelCode\Storage\Resource\Sql\SqlColumnNamesAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlFieldColumnMapAwareTrait;
use RebelCode\Storage\Resource\Sql\SqlTableAwareTrait;
use wpdb;

/**
 * Abstract base implementation of an INSERT resource model for use with WPDB.
 *
 * This generic implementation can be configured to INSERT into any table. An optional field-to-column map may be
 * provided which is used to translate consumer-friendly field names in insertion data sets to  their actual column
 * counterpart names.
 *
 * @since [*next-version*]
 */
abstract class AbstractBaseWpdbInsertResourceModel extends AbstractWpdbResourceModel implements InsertCapableInterface
{
    /*
     * Provides WPDB SQL INSERT functionality.
     *
     * @since [*next-version*]
     */
    use InsertCapableWpdbTrait;

    /*
     * Provides functionality for retrieving the last inserted ID from WPDB.
     *
     * @since [*next-version*]
     */
    use GetWpdbLastInsertedIdCapableTrait;

    /*
     * Provides functionality for building INSERT SQL queries.
     *
     * @since [*next-version*]
     */
    use BuildInsertSqlCapableTrait;

    /*
     * Provides functionality for the VALUES in INSERT SQL queries.
     *
     * @since [*next-version*]
     */
    use BuildSqlRecordValuesCapableTrait;

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
     * Provides WPDB value hash generation functionality.
     *
     * @since [*next-version*]
     */
    use GetWpdbValueHashStringCapableTrait;

    /*
     * Provides SQL table list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlTableAwareTrait;

    /*
     * Provides SQL field-to-column map storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlFieldColumnMapAwareTrait;

    /*
     * Provides SQL column name list storage functionality.
     *
     * @since [*next-version*]
     */
    use SqlColumnNamesAwareTrait;

    /*
     * Provides functionality for reading from any kind of container.
     *
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * Provides functionality for checking for key existence in any kind of container.
     *
     * @since [*next-version*]
     */
    use ContainerHasCapableTrait;

    /*
     * Provides functionality for normalizing containers.
     *
     * @since [*next-version*]
     */
    use NormalizeContainerCapableTrait;

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
     * Provides SQL value normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeSqlValueCapableTrait;

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
     * Provides functionality for creating container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * Provides functionality for creating not-found container exceptions.
     *
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * Provides string translating functionality.
     *
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * The bulk insertion flag.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $insertBulk;

    /**
     * Initializes the instance.
     *
     * @since [*next-version*]
     *
     * @param wpdb                  $wpdb           The WPDB instance to use to prepare and execute queries.
     * @param string|Stringable     $table          The table to insert records into.
     * @param string[]|Stringable[] $fieldColumnMap A map of field names to table column names.
     * @param bool                  $insertBulk     True to insert records in a single bulk query, false to insert them
     *                                              in separate queries.
     */
    protected function _init(wpdb $wpdb, $table, $fieldColumnMap, $insertBulk = true)
    {
        $this->_setWpdb($wpdb);
        $this->_setSqlTable($table);
        $this->_setSqlFieldColumnMap($fieldColumnMap);
        $this->insertBulk = $insertBulk;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function insert($records)
    {
        return $this->_insert($records);
    }

    /**
     * Retrieves the SQL database table name for use in SQL INSERT queries.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable The table.
     */
    protected function _getSqlInsertTable()
    {
        return $this->_getSqlTable();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlInsertColumnNames()
    {
        return $this->_getSqlColumnNames();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getSqlInsertFieldColumnMap()
    {
        return $this->_getSqlFieldColumnMap();
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

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _canWpdbInsertBulk()
    {
        return $this->insertBulk;
    }
}
