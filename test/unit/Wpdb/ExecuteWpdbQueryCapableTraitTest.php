<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb\UnitTest;

use PHPUnit_Framework_MockObject_MockObject;
use RebelCode\Storage\Resource\WordPress\Wpdb\ExecuteWpdbQueryCapableTrait as TestSubject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ExecuteWpdbQueryCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Wpdb\ExecuteWpdbQueryCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods Optional additional mock methods.
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    public function createInstance(array $methods = [])
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(
                            array_merge(
                                $methods,
                                [
                                    '_getWpdb',
                                    '_normalizeString',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();
        $mock->method('_normalizeString')->willReturnCallback(
            function($input) {
                return strval($input);
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
        $subject = $this->createInstance();

        $this->assertInternalType(
            'object',
            $subject,
            'An instance of the test subject could not be created'
        );
    }

    /**
     * Tests the execute wpdb query method to ensure that WPDB is being correctly invoked internally and that the
     * WPDB result is properly returned.
     *
     * @since [*next-version*]
     */
    public function testExecuteWpdbQuery()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $query = uniqid('query-');
        $args = [
            uniqid('arg-'),
            uniqid('arg-'),
        ];
        $prepared = uniqid('prepared-');
        $expected = [
            uniqid('result-'),
            uniqid('result-'),
        ];

        // Mock wpdb
        $wpdb = $this->getMockBuilder('stdClass')
                     ->setMethods(['prepare', 'query'])
                     ->getMockForAbstractClass();
        $wpdb->expects($this->atLeastOnce())
             ->method('prepare')
             ->with($query, $args)
             ->willReturn($prepared);
        $wpdb->expects($this->once())
             ->method('query')
             ->with($prepared)
             ->willReturn($expected);

        $subject->method('_getWpdb')->willReturn($wpdb);

        $actual = $reflect->_executeWpdbQuery($query, $args);

        $this->assertEquals($expected, $actual, 'Expected and retrieved results do not match');
    }
}
