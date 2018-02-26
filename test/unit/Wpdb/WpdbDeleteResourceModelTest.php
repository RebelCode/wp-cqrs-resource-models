<?php

namespace RebelCode\Storage\Resource\FuncTest\Pdo;

use Dhii\Output\TemplateInterface;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\WordPress\Wpdb\WpdbDeleteResourceModel as TestSubject;
use wpdb;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpdbDeleteResourceModelTest extends TestCase
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
                    empty($interfaces) ? '' : 'implements ' . implode(', ', $interfaces),
                ]
            );
            eval($definition);
        }

        return $this->getMockBuilder($className)
                    ->setMethods(['prepare', 'query'])
                    ->getMockForAbstractClass();
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
    public function testCanBeCreated()
    {
        $table = uniqid('table-');
        $fcMap = [
            uniqid('field-') => uniqid('column-'),
            uniqid('field-') => uniqid('column-'),
            uniqid('field-') => uniqid('column-'),
        ];
        $subject = new TestSubject($this->createWpdb(), $this->createTemplate(), $table, $fcMap);

        $this->assertInstanceOf(
            'Dhii\Storage\Resource\DeleteCapableInterface',
            $subject,
            'Test subject does not implement expected interface'
        );
    }

    /**
     * Tests the delete method to assert whether the query and value hash map are correctly built and passed to WPDB.
     *
     * @since [*next-version*]
     */
    public function testDelete()
    {
        $wpdb = $this->createWpdb();
        $table = 'users';
        $fcMap = [
            'id'   => 'id',
            'name' => 'user_name',
            'age'  => 'user_age',
        ];
        $template = $this->createTemplate();

        $subject = new TestSubject($wpdb, $template, $table, $fcMap);

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
        $template->expects($this->once())
                 ->method('render')
                 ->with($this->contains($condition))
                 ->willReturn($where = '`users`.`user_age` >= %1$d AND `users`.`user_age` <= %2$d');

        $expectedQuery = "DELETE FROM `users` WHERE $where;";
        $expectedArgs = [
            '%1$d' => 20,
            '%2$d' => 30,
        ];

        $preparedQuery = uniqid('prepared-query-');
        $wpdb->expects($this->once())
             ->method('prepare')
             ->with($expectedQuery, $expectedArgs)
             ->willReturn($preparedQuery);

        $wpdb->expects($this->once())
             ->method('query')
             ->with($preparedQuery);

        $subject->delete($condition);
    }
}
