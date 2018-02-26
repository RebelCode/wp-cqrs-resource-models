<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb\UnitTest;

use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Storage\Resource\WordPress\Wpdb\InsertCapableWpdbTrait as TestSubject;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class InsertCapableWpdbTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Wpdb\InsertCapableWpdbTrait';

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
                '_containerGet',
                '_containerHas',
                '_getWpdbValueHashString',
                '_buildInsertSql',
                '_getSqlInsertTable',
                '_getSqlInsertColumnNames',
                '_getSqlInsertFieldColumnMap',
                '_executeWpdbQuery',
                '_normalizeString',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('_containerGet')->willReturnCallback(
            function($c, $k) {
                if (!isset($c[$k])) {
                    throw $this->createNotFoundException();
                }

                return $c[$k];
            }
        );
        $mock->method('_containerHas')->willReturnCallback(
            function($c, $k) {
                return isset($c[$k]);
            }
        );
        $mock->method('_normalizeString')->willReturnCallback(
            function($s) {
                return strval($s);
            }
        );

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
     * @return object The object that extends and implements the specified class and interfaces.
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

        return $this->getMockForAbstractClass($paddingClassName);
    }

    /**
     * Creates a "not found" container exception mock instance.
     *
     * @since [*next-version*]
     *
     * @return NotFoundExceptionInterface The created instance.
     */
    public function createNotFoundException()
    {
        return $this->mockClassAndInterfaces('Exception', ['Psr\Container\NotFoundExceptionInterface']);
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
     * Tests the insert method to ensure that the value hash map is correctly generated, all the required information is
     * given to the SQL builder method and the query execution method.
     *
     * @since [*next-version*]
     */
    public function testInsert()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $table = uniqid('table-');
        $columns = [
            $c1 = uniqid('column-'),
            $c2 = uniqid('column-'),
            $c3 = uniqid('column-'),
        ];
        $fieldColMap = [
            $f1 = uniqid('field-') => $c1,
            $f2 = uniqid('field-') => $c2,
            $f3 = uniqid('field-') => $c3,
        ];
        $input = [
            [
                $f1 => $r1v1 = uniqid('value-'),
                $f2 => $r1v2 = uniqid('value-'),
                $f3 => $r1v3 = uniqid('value-'),
            ],
            [
                $f2 => $r2v1 = uniqid('value-'),
                $f3 => $r2v2 = uniqid('value-'),
            ],
        ];
        $hash = uniqid('hash-');
        $expectedValueHashMap = [
            $r1v1 => $hash,
            $r1v2 => $hash,
            $r1v3 => $hash,
            $r2v1 => $hash,
            $r2v2 => $hash,
        ];
        $expectedRowSet = [
            [
                $c1 => $r1v1,
                $c2 => $r1v2,
                $c3 => $r1v3,
            ],
            [
                $c2 => $r2v1,
                $c3 => $r2v2,
            ],
        ];

        $subject->method('_getSqlInsertTable')->willReturn($table);
        $subject->method('_getSqlInsertColumnNames')->willReturn($columns);
        $subject->method('_getSqlInsertFieldColumnMap')->willReturn($fieldColMap);
        $subject->method('_getWpdbValueHashString')->willReturn($hash);

        $subject->expects($this->atLeastOnce())
                ->method('_buildInsertSql')
                ->with($table, $columns, $expectedRowSet, $expectedValueHashMap)
                ->willReturn($query = uniqid('query-'));

        $subject->expects($this->once())
                ->method('_executeWpdbQuery')
                ->with($query, array_flip($expectedValueHashMap));

        $reflect->_insert($input);
    }
}
