<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb\UnitTest;

use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\WordPress\Wpdb\WpdbAwareTrait as TestSubject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpdbAwareTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Wpdb\WpdbAwareTrait';

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
     * Creates a mock WPDB instance.
     *
     * @since [*next-version*]
     *
     * @return MockObject
     */
    public function createWpdb()
    {
        $className = 'wpdb';
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

        return $this->getMockBuilder($className)->getMockForAbstractClass();
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
    public function testGetSetWpdb()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = $this->createWpdb();

        $reflect->_setWpdb($input);

        $this->assertSame($input, $reflect->_getWpdb(), 'Set and retrieved value are not the same.');
    }

    /**
     * Tests the getter and setter methods with a null value to assert whether it is also stored successfully.
     *
     * @since [*next-version*]
     */
    public function testGetSetWpdbNull()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = null;

        $reflect->_setWpdb($input);

        $this->assertNull($reflect->_getWpdb(), 'Retrieved value is not null.');
    }

    /**
     * Tests the getter and setter methods with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testGetSetWpdbInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);
        $input = new stdClass();

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_setWpdb($input);
    }
}
