<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb\UnitTest;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use InvalidArgumentException;
use RebelCode\Storage\Resource\WordPress\Wpdb\UpdateCapableWpdbTrait as TestSubject;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class UpdateCapableWpdbTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Wpdb\UpdateCapableWpdbTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues(
            $methods,
            [
                '_getWpdbValueHashString',
                '_buildUpdateSql',
                '_getSqlUpdateTable',
                '_getSqlUpdateFieldColumnMap',
                '_getWpdbExpressionHashMap',
                '_executeWpdbQuery',
                '_countIterable',
                '_normalizeString',
                '_createInvalidArgumentException',
                '__',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
            }
        );
        $mock->method('_normalizeString')->willReturnCallback(
            function($s) {
                return strval($s);
            }
        );
        $mock->method('__')->will($this->returnArgument(0));

        return $mock;
    }

    /**
     * Creates a mock OrderInterface instance.
     *
     * @since [*next-version*]
     *
     * @param string $entity The entity.
     * @param string $field  The field.
     * @param bool   $isAsc  The ascending flag.
     *
     * @return MockObject|OrderInterface The created instance.
     */
    public function createOrdering($entity = '', $field = '', $isAsc = true)
    {
        $mock = $this->getMockBuilder('Dhii\Storage\Resource\Sql\OrderInterface')
                     ->setMethods(
                         [
                             'getEntity',
                             'getField',
                             'isAscending',
                         ]
                     )
                     ->getMockForAbstractClass();

        $mock->method('getEntity')->willReturn($entity);
        $mock->method('getField')->willReturn($field);
        $mock->method('isAscending')->willReturn($isAsc);

        return $mock;
    }

    /**
     * Merges the values of two arrays.
     *
     * The resulting product will be a numeric array where the values of both inputs are present, without duplicates.
     *
     * @since [*next-version*]
     *
     * @param array $destination The base array.
     * @param array $source      The array with more keys.
     *
     * @return array The array which contains unique values
     */
    public function mergeValues($destination, $source)
    {
        return array_keys(array_merge(array_flip($destination), array_flip($source)));
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testCanBeCreated()
    {
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type    The expression type.
     * @param array  $terms   The expression terms.
     * @param bool   $negated Optional negation flag.
     *
     * @return LogicalExpressionInterface The created expression instance.
     */
    public function createLogicalExpression($type, $terms, $negated = false)
    {
        return $this->mock('Dhii\Expression\LogicalExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
                    ->isNegated($negated)
                    ->new();
    }

    /**
     * Tests the update method to assert whether the hash maps are correctly generated and the correct query is built
     * and executed.
     *
     * @since [*next-version*]
     */
    public function testUpdate()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // Arguments
        $condition = $this->createLogicalExpression('', []);
        $changeSet = [
            $field1 = uniqid('field-') => $val1 = uniqid('value-'),
            $field2 = uniqid('field-') => $val2 = uniqid('value-'),
            $field3 = uniqid('field-') => $val3 = uniqid('value-'),
        ];
        $ordering = [
            $this->createOrdering(),
            $this->createOrdering(),
        ];
        $limit = rand(0, 100);

        // Count is larger than 0
        $subject->method('_countIterable')->willReturn(count($changeSet));

        // Table, fields and field-column map mocking
        $table = uniqid('table-');
        $fields = [$field1, $field2, $field3];
        $fieldColumnMap = [
            $field1 => $col1 = uniqid('column-'),
            $field2 => $col2 = uniqid('column-'),
            $field3 => $col3 = uniqid('column-'),
        ];
        $subject->method('_getSqlUpdateTable')->willReturn($table);
        $subject->method('_getSqlUpdateFieldColumnMap')->willReturn($fieldColumnMap);

        // hash map generated for condition
        $cHashMap = [
            $condVal1 = uniqid('value-') => $condHash1 = uniqid('hash-'),
            $condVal2 = uniqid('value-') => $condHash2 = uniqid('hash-'),
            $condVal3 = uniqid('value-') => $condHash3 = uniqid('hash-'),
        ];
        $subject->method('_getWpdbExpressionHashMap')->with($condition, $fields)->willReturn($cHashMap);

        // Hashes for the change set
        $subject->method('_getWpdbValueHashString')->willReturnOnConsecutiveCalls(
            $hash1 = uniqid('hash-'),
            $hash2 = uniqid('hash-'),
            $hash3 = uniqid('hash-')
        );

        // Query to return from query builder
        $query = uniqid('query-');
        // Hash map expected to be received by the query builder
        $expectedHashMap = [
            $condVal1 => $condHash1,
            $condVal2 => $condHash2,
            $condVal3 => $condHash3,
            $val1     => $hash1,
            $val2     => $hash2,
            $val3     => $hash3,
        ];
        $processedChangeSet = [
            $col1 => $val1,
            $col2 => $val2,
            $col3 => $val3,
        ];
        $subject->expects($this->atLeastOnce())
                ->method('_buildUpdateSql')
                ->with($table, $processedChangeSet, $condition, $ordering, $limit, $expectedHashMap)
                ->willReturn($query);

        // Expectation for query execution
        $subject->expects($this->once())
                ->method('_executeWpdbQuery')
                ->with(
                    $query,
                    array_flip($expectedHashMap)
                );

        $reflect->_update($changeSet, $condition, $ordering, $limit);
    }
}
