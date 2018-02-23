<?php

namespace RebelCode\Storage\Resource\WordPress\Posts\FuncTest;

use Dhii\Util\String\StringableInterface;
use \InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use stdClass;
use Xpmock\TestCase;
use RebelCode\Storage\Resource\WordPress\Posts\PostMetaFieldKeyAwareTrait as TestSubject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class PostMetaFieldKeyAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Posts\PostMetaFieldKeyAwareTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @return MockObject
     */
    public function createInstance()
    {
        // Create mock
        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods(['__', '_createInvalidArgumentException'])
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($msg = '', $code = 0, $prev = null) {
                return new InvalidArgumentException($msg, $code, $prev);
            }
        );

        return $mock;
    }

    /**
     * Creates a new mock stringable instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject|StringableInterface The mock stringable instance.
     */
    public function createStringable()
    {
        return $this->getMockBuilder('Dhii\Util\String\StringableInterface')
                    ->setMethods(['__toString'])
                    ->getMockForAbstractClass();
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
     * Tests the getter and setter methods with a string to assert whether it is correctly stored and retrieved.
     *
     * @since [*next-version*]
     */
    public function testGetSetPostMetaFieldKeyString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = uniqid('key-');

        $reflect->_setPostMetaFieldKey($input);

        $this->assertEquals($input, $reflect->_getPostMetaFieldKey(), 'Set and retrieved values do not match.');
    }

    /**
     * Tests the getter and setter methods with a stringable instance to assert whether it is correctly stored and
     * retrieved.
     *
     * @since [*next-version*]
     */
    public function testGetSetPostMetaFieldKeyStringable()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $input = $this->createStringable();
        $input->method('__toString')->willReturn($input);

        $reflect->_setPostMetaFieldKey($input);

        $this->assertSame($input, $reflect->_getPostMetaFieldKey(), 'Set and retrieved values are not the same.');
    }

    /**
     * Tests the getter and setter methods with a null value to assert whether null is accepted and retrievable.
     *
     * @since [*next-version*]
     */
    public function testGetSetPostMetaFieldKeyNull()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $reflect->_setPostMetaFieldKey(null);

        $this->assertNull($reflect->_getPostMetaFieldKey(), 'Retrieved value is not null.');
    }

    /**
     * Tests the getter and setter methods with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetPostMetaFieldKeyInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = new stdClass();

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setPostMetaFieldKey($input);
    }
}
