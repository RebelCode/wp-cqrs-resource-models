<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb\UnitTest;

use InvalidArgumentException;
use RebelCode\Storage\Resource\WordPress\Wpdb\GetWpdbValueHashStringCapableTrait as TestSubject;
use stdClass;
use Xpmock\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class GetWpdbValueHashStringCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Wpdb\GetWpdbValueHashStringCapableTrait';

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
                '_normalizeString',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('_normalizeString')->willReturnCallback(
            function ($s) {
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
     * Tests the WPDB value hash string  method with a string value to assert whether the retrieved hash string
     * contains the correct type indicator and position.
     *
     * @since [*next-version*]
     */
    public function testGetWpdbValueHashString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value    = uniqid('value-');
        $position = rand(1, 10);

        $expected = ':' . $position . ':' . hash('crc32b', $value);
        $hash     = $reflect->_getWpdbValueHashString($value, $position);

        $this->assertEquals($expected, $hash, 'Expected and retrieved hash strings do not match.');
    }
}
