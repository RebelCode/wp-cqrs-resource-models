<?php

namespace RebelCode\Storage\Resource\WordPress\Posts\UnitTest;

use RebelCode\Storage\Resource\WordPress\Posts\InsertCapableWpTrait as TestSubject;
use Xpmock\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpInsertCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Posts\InsertCapableWpTrait';

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
                '_normalizeWpPostDataArray',
                '_wpInsertPost',
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
     * Tests the insert method to assert whether the input list of post data are all normalized and given to the
     * internal WordPress insertion wrapper method.
     *
     * @since [*next-version*]
     */
    public function testInsert()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        // Input array of post data
        $input = [
            $inputPost1 = [
                uniqid('key-') => uniqid('value-'),
                uniqid('key-') => uniqid('value-'),
            ],
            $inputPost2 = [
                uniqid('key-') => uniqid('value-'),
                uniqid('key-') => uniqid('value-'),
                uniqid('key-') => uniqid('value-'),
            ],
            $inputPost3 = [
                uniqid('key-') => uniqid('value-'),
            ],
        ];
        $normalized = [
            $normPost1 = [
                uniqid('key-') => uniqid('value-'),
                uniqid('key-') => uniqid('value-'),
            ],
            $normPost2 = [
                uniqid('key-') => uniqid('value-'),
                uniqid('key-') => uniqid('value-'),
                uniqid('key-') => uniqid('value-'),
            ],
            $normPost3 = [
                uniqid('key-') => uniqid('value-'),
            ],
        ];

        $subject->expects($this->exactly(count($input)))
                ->method('_normalizeWpPostDataArray')
                ->withConsecutive([$inputPost1], [$inputPost2], [$inputPost3])
                ->willReturnOnConsecutiveCalls($normPost1, $normPost2, $normPost3);

        $subject->expects($this->exactly(count($normalized)))
                ->method('_wpInsertPost')
                ->withConsecutive([$normPost1], [$normPost2], [$normPost3]);

        $reflect->_insert($input);
    }
}
