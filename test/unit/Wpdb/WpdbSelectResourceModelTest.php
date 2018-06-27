<?php

namespace RebelCode\Storage\Resource\UnitTest\WordPress\Wpdb;

use Dhii\Collection\MapFactoryInterface;
use Dhii\Output\TemplateInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\WordPress\Wpdb\WpdbSelectResourceModel as TestSubject;
use wpdb;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpdbSelectResourceModelTest extends TestCase
{
    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string $className      Name of the class for the mock to extend.
     * @param array  $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockBuilder The mock builder for an object that extends and implements the specified class and interfaces
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf(
            'abstract class %1$s extends %2$s implements %3$s {}',
            [
                $paddingClassName,
                $className,
                implode(', ', $interfaceNames),
            ]
        );
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a mock WPDB instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject|wpdb
     */
    public function createWpdb()
    {
        $className = 'wpdb';

        if (!class_exists($className)) {
            $parentName = 'stdClass';
            $interfaces = [];
            $definition = vsprintf(
                'abstract class %1$s extends %2$s %3$s {}',
                [
                    $className,
                    $parentName,
                    empty($interfaces) ? '' : 'implements '.implode(', ', $interfaces),
                ]
            );
            eval($definition);
        }

        return $this->getMockBuilder($className)
                    ->setMethods(['prepare', 'query'])
                    ->getMockForAbstractClass();
    }

    /**
     *
     *
     * @since [*next-version*]
     *
     * @param $map
     *
     * @return MockObject
     */
    protected function createMap($map)
    {
        $builder = $this->mockClassAndInterfaces('ArrayObject', ['Dhii\Collection\MapInterface']);
        $mock    = $builder->setMethods(['get', 'has'])
                           ->setConstructorArgs([$map])
                           ->getMock();

        return $mock;
    }

    /**
     * Creates a new template mock instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject|TemplateInterface The created mock template instance.
     */
    protected function createTemplate()
    {
        return $this->getMockBuilder('Dhii\Output\TemplateInterface')
                    ->setMethods(['render'])
                    ->getMockForAbstractClass();
    }

    /**
     * Creates a new map factory instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject|MapFactoryInterface The created map factory instance.
     */
    protected function createMapFactory()
    {
        $mock = $this->getMockBuilder('Dhii\Collection\MapFactoryInterface')
                    ->setMethods(['make'])
                    ->getMockForAbstractClass();

        $mock->method('make')
             ->willReturnCallback(function ($config) {
                 return $this->createMap($config[MapFactoryInterface::K_DATA]);
             });

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
     * @return MockObject The created expression instance.
     */
    public function createLogicalExpression($type, $terms, $negated = false)
    {
        $mock = $this->getMockBuilder('Dhii\Expression\LogicalExpressionInterface')
                     ->setMethods(['getTerms', 'getType', 'isNegated'])
                     ->getMockForAbstractClass();

        $mock->method('getType')->willReturn($type);
        $mock->method('getTerms')->willReturn($terms);
        $mock->method('isNegated')->willReturn($negated);

        return $mock;
    }

    /**
     * Creates an entity field mock instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $entity The entity name.
     * @param string|Stringable $field  The field name.
     * @param string|Stringable $type   The term type.
     *
     * @return MockObject The created entity field term instance.
     */
    public function createEntityFieldTerm($entity, $field, $type = '')
    {
        $builder = $this->mockClassAndInterfaces(
            'stdClass',
            [
                'Dhii\Expression\TermInterface',
                'Dhii\Storage\Resource\Sql\EntityFieldInterface',
            ]
        );
        $builder->setMethods(['getEntity', 'getField', 'getType']);

        $mock = $builder->getMockForAbstractClass();

        $mock->method('getEntity')->willReturn($entity);
        $mock->method('getField')->willReturn($field);
        $mock->method('getType')->willReturn($type);

        return $mock;
    }

    /**
     * Creates a literal term mock instance.
     *
     * @since [*next-version*]
     *
     * @param mixed $value The value.
     *
     * @return MockObject The created literal term mock instance.
     */
    public function createLiteralTerm($value)
    {
        $mock = $this->getMockBuilder('Dhii\Expression\LiteralTermInterface')
                     ->setMethods(['getValue'])
                     ->getMockForAbstractClass();

        $mock->method('getValue')->willReturn($value);

        return $mock;
    }

    /**
     * Tests whether a valid instance of the test subject can be created.
     *
     * @since [*next-version*]
     */
    public function testConstructor()
    {
        $wpdb = $this->createWpdb();
        $template = $this->createTemplate();
        $mapFactory = $this->createMapFactory();
        $tables = [
            uniqid('table-') => uniqid('alias-'),
            uniqid('table-') => uniqid('alias-'),
        ];
        $fcMap = [
            'id' => 'id',
            'name' => 'user_name',
            'age' => 'user_age',
            'id2' => 'id_2',
        ];
        $joins = [
            uniqid('table-') => $this->createLogicalExpression(uniqid('type-'), []),
            uniqid('table-') => $this->createLogicalExpression(uniqid('type-'), []),
        ];

        $subject = new TestSubject($wpdb, $template, $mapFactory, $tables, $fcMap, $joins);

        $this->assertInstanceOf(
            'Dhii\Storage\Resource\SelectCapableInterface',
            $subject,
            'Test subject does not implement expected interface.'
        );
    }

    /**
     * Tests the SELECT functionality to assert that the query is given to WPDB for preparing and querying.
     *
     * @since [*next-version*]
     */
    public function testSelect()
    {
        $wpdb = $this->createWpdb();
        $tables = [
            'my_users' => 'users'
        ];
        $fcMap = [
            'id' => 'id',
            'name' => 'user_name',
            'age' => 'user_age',
        ];
        $template = $this->createTemplate();
        $mapFactory = $this->createMapFactory();
        $joins = [];
        $subject = new TestSubject($wpdb, $template, $mapFactory, $tables, $fcMap, $joins);

        $condition = $this->createLogicalExpression(
            'and',
            [
                $this->createLogicalExpression(
                    'greater_equal_to',
                    [
                        $this->createEntityFieldTerm('users', 'age'),
                        $this->createLiteralTerm(20),
                    ]
                ),
                $this->createLogicalExpression(
                    'smaller_equal_to',
                    [
                        $this->createEntityFieldTerm('users', 'age'),
                        $this->createLiteralTerm(30),
                    ]
                ),
            ]
        );

        $template->expects($this->atLeastOnce())
                 ->method('render')
                 ->with($this->contains($condition))
                 ->willReturn($where = '`users`.`user_age` > %s AND `users`.`user_age` < %s');

        $expectedQuery =
            "SELECT `id` AS `id`, `user_name` AS `name`, `user_age` AS `age` FROM `users` AS `my_users` WHERE $where;";

        $preparedQuery = uniqid('prepared-query-');
        $wpdb->expects($this->once())
            ->method('prepare')
            ->with($expectedQuery, $this->isType('array'))
            ->willReturn($preparedQuery);

        $expected = [
            [
                uniqid('field1-') => uniqid('value1-'),
                uniqid('field2-') => uniqid('value2-'),
            ],
            [
                uniqid('field1-') => uniqid('value1-'),
                uniqid('field2-') => uniqid('value2-'),
            ],
            [
                uniqid('field1-') => uniqid('value1-'),
                uniqid('field2-') => uniqid('value2-'),
            ],
        ];
        $wpdb->expects($this->once())
            ->method('query')
            ->with($preparedQuery);
        $wpdb->last_result = $expected;

        $actual    = $subject->select($condition);
        $actualRaw = [];

        foreach ($actual as $_item) {
            $this->assertInstanceOf(
                'Dhii\Collection\MapInterface',
                $_item,
                'Item in result list is not a map'
            );

            $actualRaw[] = iterator_to_array($_item);
        }

        $this->assertEquals($expected, $actualRaw, 'Expected and retrieved results are not the same.');
    }
}
