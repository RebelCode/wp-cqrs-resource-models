<?php

namespace RebelCode\Storage\Resource\WordPress\Posts\UnitTest;

use ArrayIterator;
use Dhii\Expression\LiteralTermInterface;
use Dhii\Util\String\StringableInterface;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\WordPress\Posts\NormalizeWpPostDataArrayCapableTrait as TestSubject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class NormalizeWpPostDataArrayCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Posts\NormalizeWpPostDataArrayCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods The methods to mock.
     *
     * @return MockObject|TestSubject The new instance.
     */
    public function createInstance($methods = [])
    {
        $methods = $this->mergeValues(
            $methods,
            [
                '_containerGet',
                '_containerHas',
                '_getPostFieldKeyMap',
                '_getPostMetaFieldKey',
                '_normalizeString',
                '_createInvalidArgumentException',
                '__',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function($m, $c, $p) {
                return new InvalidArgumentException($m, $c, $p);
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
     * Creates a stringable mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $string The string.
     *
     * @return StringableInterface The created stringable instance.
     */
    public function createStringable($string)
    {
        return $this->mock('Dhii\Util\String\StringableInterface')
                    ->__toString($string)
                    ->new();
    }

    /**
     * Creates a term mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type The term type.
     *
     * @return LiteralTermInterface The created term instance.
     */
    public function createTerm($type = '')
    {
        return $this->mock('Dhii\Expression\TermInterface')
                    ->getType($type)
                    ->new();
    }

    /**
     * Creates a literal term mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type  The term type.
     * @param mixed  $value The term value.
     *
     * @return LiteralTermInterface The created literal term instance.
     */
    public function createLiteralTerm($type, $value)
    {
        return $this->mock('Dhii\Expression\LiteralTermInterface')
                    ->getType($type)
                    ->getValue($value)
                    ->new();
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
     * Tests the post data value normalization method with an integer value to assert whether the result is as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataValueInteger()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = rand(1, 100);
        $expected = $value;
        $actual = $reflect->_normalizeWpPostDataValue($value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the post data value normalization method with a float value to assert whether the result is as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataValueFloat()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = floatval(rand(1, 100)) / floatval(rand(1, 100));
        $expected = $value;
        $actual = $reflect->_normalizeWpPostDataValue($value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the post data value normalization method with a boolean value to assert whether the result is as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataValueBoolean()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = rand(0, 1) === 1;
        $expected = $value;
        $actual = $reflect->_normalizeWpPostDataValue($value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the post data value normalization method with a string value to assert whether the result is as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataValueString()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = uniqid('string-');
        $expected = $value;
        $actual = $reflect->_normalizeWpPostDataValue($value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the post data value normalization method with a literal term value to assert whether the result is as
     * expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataValueLiteralTerm()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expected = uniqid('string-');
        $value = $this->createLiteralTerm('', $expected);

        $actual = $reflect->_normalizeWpPostDataValue($value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the post data value normalization method with a stringable value to assert whether the result is as
     * expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataValueStringable()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expected = uniqid('string-');
        $value = $this->createStringable($expected);

        $subject->method('_normalizeString')->willReturn($expected);

        $actual = $reflect->_normalizeWpPostDataValue($value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the post data value normalization method with an array value to assert whether the result is as expected.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataValueArray()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $subject->method('_normalizeString')->willReturnCallback('strval');

        $value = [
            $exp1 = uniqid('string-'),
            $exp2 = rand(1, 100),
            $this->createStringable($exp3 = uniqid('string-')),
            $exp4 = floatval(rand(1, 00)) / floatval(rand(1, 00)),
            $this->createLiteralTerm('', $exp5 = uniqid('value-')),
        ];
        $expected = [
            $exp1,
            $exp2,
            $exp3,
            $exp4,
            $exp5,
        ];
        $actual = $reflect->_normalizeWpPostDataValue($value);

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests the post data value normalization method with a non-literal term value to assert whether an exception is
     * thrown.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataValueTerm()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = $this->createTerm();

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_normalizeWpPostDataValue($value);
    }

    /**
     * Tests the post data value normalization method with an invalid value to assert whether an exception is thrown.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataValueInvalid()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $value = new stdClass();

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_normalizeWpPostDataValue($value);
    }

    /**
     * Tests the post data and meta normalization method with a traversable argument to assert whether the output is
     * correct and also has correct separation between post data and meta data.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataAndMeta()
    {
        $subject = $this->createInstance(['_normalizeWpPostDataValue']);
        $reflect = $this->reflect($subject);

        $field1 = uniqid('field-');
        $field2 = uniqid('field-');
        $field3 = uniqid('field-');
        $column1 = uniqid('column-');
        $column2 = uniqid('column-');
        $column3 = uniqid('column-');
        $meta1 = uniqid('meta-');
        $meta2 = uniqid('meta-');
        $value1 = uniqid('value-');
        $value2 = uniqid('value-');
        $value3 = uniqid('value-');
        $value4 = uniqid('value-');
        $value5 = uniqid('value-');

        $traversable = new ArrayIterator(
            $array = [
                $field1 => $value1,
                $field2 => $value2,
                $field3 => $value3,
                $meta1  => $value4,
                $meta2  => $value5,
            ]
        );

        $fieldsMap = [
            $field1 => $column1,
            $field2 => $column2,
            $field3 => $column3,
        ];
        $subject->method('_getPostFieldKeyMap')->willReturn($fieldsMap);

        $metaField = uniqid('meta-');
        $subject->method('_getPostMetaFieldKey')->willReturn($metaField);

        $expected = [
            $column1   => $value1,
            $column2   => $value2,
            $column3   => $value3,
            $metaField => [
                $meta1 => $value4,
                $meta2 => $value5,
            ],
        ];

        $subject->method('_normalizeString')->willReturnArgument(0);

        $subject->expects($this->exactly(count($array)))
                ->method('_normalizeWpPostDataValue')
                ->willReturnArgument(0);

        $result = $reflect->_normalizeWpPostDataAndMeta($traversable);

        $this->assertEquals($expected, $result, 'Expected and retrieved post data arrays do not match.');
    }

    /**
     * Tests the post data array normalization method with a container argument to assert whether the output is
     * correct and meta data is ignored.
     *
     * @since [*next-version*]
     */
    public function testNormalizeWpPostDataArrayContainer()
    {
        $subject = $this->createInstance(['_normalizeWpPostDataValue']);
        $reflect = $this->reflect($subject);

        $field1 = uniqid('field-');
        $field2 = uniqid('field-');
        $field3 = uniqid('field-');
        $column1 = uniqid('column-');
        $column2 = uniqid('column-');
        $column3 = uniqid('column-');
        $meta1 = uniqid('meta-');
        $meta2 = uniqid('meta-');
        $value1 = uniqid('value-');
        $value2 = uniqid('value-');
        $value3 = uniqid('value-');
        $value4 = uniqid('value-');
        $value5 = uniqid('value-');
        $numMeta = 2; // number of meta entries

        $array = [
            $field1 => $value1,
            $field2 => $value2,
            $field3 => $value3,
            $meta1  => $value4,
            $meta2  => $value5,
        ];
        $container = (object) $array;

        $fieldsMap = [
            $field1 => $column1,
            $field2 => $column2,
            $field3 => $column3,
        ];
        $subject->method('_getPostFieldKeyMap')->willReturn($fieldsMap);

        $metaField = uniqid('meta-');
        $subject->method('_getPostMetaFieldKey')->willReturn($metaField);

        $expected = [
            $column1   => $value1,
            $column2   => $value2,
            $column3   => $value3,
            $metaField => [],
        ];

        $subject->method('_normalizeString')->willReturnArgument(0);

        $subject->expects($this->exactly(count($array) - $numMeta))
                ->method('_normalizeWpPostDataValue')
                ->willReturnArgument(0);

        $subject->method('_containerHas')->willReturnCallback(
            function($c, $k) {
                return property_exists($c, $k);
            }
        );

        $subject->method('_containerGet')->willReturnCallback(
            function($c, $k) {
                return $c->{$k};
            }
        );

        $result = $reflect->_normalizeWpPostDataArray($container);

        $this->assertEquals($expected, $result, 'Expected and retrieved post data arrays do not match.');
    }
}
