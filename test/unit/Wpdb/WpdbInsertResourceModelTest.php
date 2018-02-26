<?php

namespace RebelCode\Storage\Resource\FuncTest\Pdo;

use Dhii\Data\Container\Exception\NotFoundException;
use PDO;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\WordPress\Wpdb\WpdbInsertResourceModel as TestSubject;
use Xpmock\TestCase;
use wpdb;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpdbInsertResourceModelTest extends TestCase
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
     * Creates a new mock container instance.
     *
     * @since [*next-version*]
     *
     * @param array $data The container data.
     *
     * @return MockObject
     */
    public function createContainer($data = [])
    {
        $mock = $this->getMockBuilder('Psr\Container\ContainerInterface')
                     ->setMethods(['get', 'has'])
                     ->getMockForAbstractClass();

        $mock->method('get')->willReturnCallback(
            function($k) use ($data, $mock) {
                if (isset($data[$k])) {
                    return $data[$k];
                }
                throw new NotFoundException(null, null, null, $mock, $k);
            }
        );

        $mock->method('has')->willReturnCallback(
            function($k) use ($data) {
                return isset($data[$k]);
            }
        );

        return $mock;
    }

    /**
     * Creates a new ArrayAccess mock instance.
     *
     * @since [*next-version*]
     *
     * @param array $data The array access data.
     *
     * @return MockObject
     */
    public function createArrayAccess($data = [])
    {
        $mock = $this->getMockBuilder('ArrayAccess')
                     ->setMethods(['offsetGet', 'offsetExists', 'offsetSet', 'offsetUnset'])
                     ->getMockForAbstractClass();

        $mock->method('offsetGet')->willReturnCallback(
            function($k) use ($data, $mock) {
                return (isset($data[$k]))
                    ? $data[$k]
                    : null;
            }
        );

        $mock->method('offsetExists')->willReturnCallback(
            function($k) use ($data) {
                return isset($data[$k]);
            }
        );

        return $mock;
    }

    /**
     * Creates a mock iterator instance.
     *
     * @since [*next-version*]
     *
     * @param array $data The data to iterate over.
     *
     * @return MockObject
     */
    public function createIterator($data = [])
    {
        $mock = $this->getMockBuilder('Iterator')
                     ->setMethods(['rewind', 'current', 'key', 'next', 'valid'])
                     ->getMockForAbstractClass();

        $mock->method('rewind')->willReturnCallback(
            function() use (&$data) {
                return reset($data);
            }
        );
        $mock->method('current')->willReturnCallback(
            function() use (&$data) {
                return current($data);
            }
        );
        $mock->method('key')->willReturnCallback(
            function() use (&$data) {
                return key($data);
            }
        );
        $mock->method('next')->willReturnCallback(
            function() use (&$data) {
                return next($data);
            }
        );
        $mock->method('valid')->willReturnCallback(
            function() use (&$data) {
                return key($data) !== null;
            }
        );

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
        $subject = new TestSubject($this->createWpdb(), $table, $fcMap);

        $this->assertInstanceOf(
            'Dhii\Storage\Resource\InsertCapableInterface',
            $subject,
            'Test subject does not implement expected interface'
        );
    }

    /**
     * Tests the insert method to assert whether the query and records are properly built and passed to WPDB.
     *
     * @since [*next-version*]
     */
    public function testInsert()
    {
        $wpdb = $this->createWpdb();
        $table = 'users';
        $fcMap = [
            'id'   => 'id',
            'name' => 'user_name',
            'age'  => 'user_age',
        ];
        $subject = new TestSubject($wpdb, $table, $fcMap);

        $records = [
            $record1 = [
                'id'   => 2,
                'name' => 'foo',
                'age'  => 28,
            ],
        ];

        $expectedQuery = 'INSERT INTO `users` (`id`, `user_name`, `user_age`) VALUES (%1$d, %2$s, %3$d);';
        $expectedArgs = [
            '%1$d' => 2,
            '%2$s' => 'foo',
            '%3$d' => 28,
        ];

        $preparedQuery = uniqid('prepared-query-');
        $wpdb->expects($this->once())
             ->method('prepare')
             ->with($expectedQuery, $expectedArgs)
             ->willReturn($preparedQuery);

        $wpdb->expects($this->once())
             ->method('query')
             ->with($preparedQuery);

        $subject->insert($records);
    }
}
