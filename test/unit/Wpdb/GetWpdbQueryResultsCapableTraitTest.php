<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb\UnitTest;

use RebelCode\Storage\Resource\WordPress\Wpdb\GetWpdbQueryResultsCapableTrait as TestSubject;
use Xpmock\TestCase;
use Exception as RootException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class GetWpdbQueryResultsCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Wpdb\GetWpdbQueryResultsCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return TestSubject|MockObject The new instance.
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues(
            $methods,
            [
                '_getWpdb',
                '_executeWpdbQuery',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

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
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockObject The object that extends and implements the specified class and interfaces.
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
     * Creates a new exception.
     *
     * @since [*next-version*]
     *
     * @param string $message The exception message.
     *
     * @return RootException|MockObject The new exception.
     */
    public function createException($message = '')
    {
        $mock = $this->getMockBuilder('Exception')
                     ->setConstructorArgs([$message])
                     ->getMock();

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
            'A valid instance of the test subject could not be created.'
        );
    }

    /**
     * Tests the `_getWpdbQueryResults()` method to assert whether the results are correctly retrieved after invoking
     * the query with WPDB.
     *
     * @since [*next-version*]
     */
    public function testGetWpdbQueryResults()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $query = uniqid('query-');
        $args = [
            uniqid('arg-'),
            uniqid('arg-'),
        ];
        $wpdb = $this->getMockBuilder('stdClass')
                     ->setMethods([])
                     ->getMock();
        $wpdb->last_result = $expected = [
            uniqid('result-'),
            uniqid('result-'),
            uniqid('result-'),
        ];

        $subject->expects($this->once())
                ->method('_executeWpdbQuery')
                ->with($query, $args);

        $subject->expects($this->once())
                ->method('_getWpdb')
                ->willReturn($wpdb);

        $actual = $reflect->_getWpdbQueryResults($query, $args);

        $this->assertSame($expected, $actual, 'Retrieved query result is not expected result.');
    }
}
