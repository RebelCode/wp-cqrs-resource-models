<?php

namespace RebelCode\Storage\Resource\WordPress\Posts\FuncTest;

use Exception;
use InvalidArgumentException;
use OutOfRangeException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\WordPress\Posts\PostFieldKeyMapAwareTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class PostFieldKeyMapAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Posts\PostFieldKeyMapAwareTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return MockObject|TestSubject
     */
    public function createInstance()
    {
        // Create mock
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods(
                         [
                             '_mapIterable',
                             '__',
                             '_createInvalidArgumentException',
                             '_createOutOfRangeException',
                         ]
                     )
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($msg = '', $code = 0, $prev = null) {
                return new InvalidArgumentException($msg, $code, $prev);
            }
        );
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($msg = '', $code = 0, $prev = null) {
                return new OutOfRangeException($msg, $code, $prev);
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
     * Tests the getter and setter methods to ensure correct assignment and retrieval.
     *
     * @since [*next-version*]
     */
    public function testGetSetPostFieldKeyMap()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $input = [
            uniqid('field-') => uniqid('key-'),
            uniqid('field-') => uniqid('key-'),
            uniqid('field-') => uniqid('key-'),
        ];
        $expected = [
            uniqid('field-') => uniqid('key-'),
            uniqid('field-') => uniqid('key-'),
            uniqid('field-') => uniqid('key-'),
        ];

        $subject->expects($this->once())->method('_mapIterable')->willReturnCallback(
            function($iterable, $callback, $start, $end, &$results) use ($expected) {
                $results = $expected;
            }
        );

        $reflect->_setPostFieldKeyMap($input);

        $this->assertEquals($expected, $reflect->_getPostFieldKeyMap(), 'Set and retrieved value do not match.');
    }

    /**
     * Tests the getter and setter methods when the internal map iteration method throws an OutOfRangeException to
     * assert whether it is re-thrown or allowed to bubble up.
     *
     * @since [*next-version*]
     */
    public function testGetSetPostFieldKeyMapOutOfRangeException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $input = [
            uniqid('field-') => uniqid('key-'),
            uniqid('field-') => uniqid('key-'),
            uniqid('field-') => uniqid('key-'),
        ];

        $subject->expects($this->once())->method('_mapIterable')
                ->willThrowException($exception = new OutOfRangeException());

        $this->setExpectedException('OutOfRangeException');

        $reflect->_setPostFieldKeyMap($input);
    }

    /**
     * Tests the getter and setter methods when the internal map iteration method throws an InvalidArgumentException to
     * assert whether it is re-thrown or allowed to bubble up.
     *
     * @since [*next-version*]
     */
    public function testGetSetPostFieldKeyMapInvalidArgumentException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = new Exception();

        $subject->expects($this->once())->method('_mapIterable')
                ->willThrowException($exception = new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setPostFieldKeyMap($input);
    }
}
