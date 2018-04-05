<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb\UnitTest;

use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Storage\Resource\Sql\OrderInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class SelectCapableWpdbTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Wpdb\SelectCapableWpdbTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods Optional additional mock methods.
     *
     * @return MockObject
     */
    public function createInstance(array $methods = [])
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(
                            array_merge(
                                $methods,
                                [
                                    '_buildSelectSql',
                                    '_getSqlSelectTables',
                                    '_getSqlSelectColumns',
                                    '_getSqlSelectFieldNames',
                                    '_getSqlSelectJoinConditions',
                                    '_getWpdbExpressionHashMap',
                                    '_getWpdbQueryResults',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();

        return $mock;
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
     * Creates an entity field mock instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $entity The entity name.
     * @param string|Stringable $field  the field name.
     *
     * @return EntityFieldInterface
     */
    public function createEntityField($entity, $field)
    {
        return $this->mock('Dhii\Storage\Resource\Sql\EntityFieldInterface')
                    ->getEntity($entity)
                    ->getField($field)
                    ->new();
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
            'An instance of the test subject could not be created'
        );
    }

    /**
     * Tests the SELECT SQL method in its simplest form: without a condition and without joins.
     *
     * @since [*next-version*]
     */
    public function testSelect()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression(uniqid('type-'), []);
        $cols = [uniqid('col-', 'col-')];
        $tables = [uniqid('table-'), uniqid('table-')];
        $fields = [uniqid('field-'), uniqid('field-')];
        $vhm = [
            // value->hash map
            $v1 = uniqid('value-') => $h1 = uniqid('hash-'),
            $v2 = uniqid('value-') => $h2 = uniqid('hash-'),
        ];
        $hvm = [
            // hash->value map
            $h1 => $v1,
            $h2 => $v2,
        ];
        $joins = [
            $this->createLogicalExpression(uniqid('type-'), []),
            $this->createLogicalExpression(uniqid('type-'), []),
        ];
        $ordering = [
            $this->createOrdering(),
            $this->createOrdering(),
        ];
        $limit = rand(50, 100);
        $offset = rand(0, 50);
        $query = uniqid('query-');

        $subject->method('_getSqlSelectColumns')->willReturn($cols);
        $subject->method('_getSqlSelectTables')->willReturn($tables);
        $subject->method('_getSqlSelectJoinConditions')->willReturn($joins);
        $subject->method('_getSqlSelectFieldNames')->willReturn($fields);
        $subject->expects($this->atLeastOnce())
                ->method('_getWpdbExpressionHashMap')
                ->with($condition, $fields)
                ->willReturn($vhm);

        $subject->expects($this->once())
                ->method('_buildSelectSql')
                ->with($cols, $tables, $joins, $condition, $ordering, $limit, $offset, $vhm)
                ->willReturn($query);

        $expected = [
            ['id' => '1', 'name' => 'foo'],
            ['id' => '2', 'name' => 'bar'],
            ['id' => '4', 'name' => 'test'],
        ];

        $subject->expects($this->once())
                ->method('_getWpdbQueryResults')
                ->with($query, $hvm)
                ->willReturn($expected);

        $result = $reflect->_select($condition, $ordering, $limit, $offset);

        $this->assertEquals($expected, $result, 'Expected and retrieved results do not match');
    }
}
