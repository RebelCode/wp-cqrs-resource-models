<?php

namespace RebelCode\Storage\Resource\WordPress\Posts\UnitTest;

use Dhii\Expression\LogicalExpressionInterface;
use InvalidArgumentException;
use RebelCode\Storage\Resource\WordPress\Posts\UpdateCapableWpTrait as TestSubject;
use Xpmock\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpUpdateCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Posts\UpdateCapableWpTrait';

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
                '_extractPostIdsFromExpression',
                '_getPostIdFieldName',
                '_wpUpdatePost',
                '_createInvalidArgumentException',
                '__',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('__')->willReturnArgument(0);
        $mock->method('_createInvalidArgumentException')->willReturnCallback(
            function ($m, $c, $p) {
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
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type    The expression type.
     * @param array  $terms   The expression terms.
     * @param bool   $negated Optional negation flag.
     *
     * @return LogicalExpressionInterface The created expression instance.
     */
    public function createLogicalExpression($type, $terms, $negated = false)
    {
        return $this->mock('Dhii\Expression\LogicalExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
                    ->isNegated($negated)
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
     * Tests the update method to assert whether the changeset is correctly normalized into a correct WP post data
     * array and is used to update for each post ID extracted from the condition.
     *
     * @since [*next-version*]
     */
    public function testUpdate()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $postIdField = uniqid('post-id-');
        $subject->method('_getPostIdFieldName')->willReturn($postIdField);

        $expression = $this->createLogicalExpression(uniqid('type-'), [], false);
        $postIds = [rand(1, 500), rand(1, 500), rand(1, 500)];
        $subject->expects($this->atLeastOnce())
                ->method('_extractPostIdsFromExpression')
                ->with($expression)
                ->willReturn($postIds);

        // Input array of post data
        $input = [
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        ];
        $normalized = [
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        ];

        $subject->expects($this->atLeastOnce())
                ->method('_normalizeWpPostDataArray')
                ->with($input)
                ->willReturn($normalized);

        $expected1 = array_merge([$postIdField => $postIds[0]], $normalized);
        $expected2 = array_merge([$postIdField => $postIds[1]], $normalized);
        $expected3 = array_merge([$postIdField => $postIds[2]], $normalized);

        $subject->expects($this->exactly(count($input)))
                ->method('_wpUpdatePost')
                ->withConsecutive([$expected1], [$expected2], [$expected3]);

        $reflect->_update($input, $expression);
    }

    /**
     * Tests the update method to assert whether an exception is thrown when no condition is given.
     *
     * @since [*next-version*]
     */
    public function testUpdateNoCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $postIdField = uniqid('post-id-');
        $subject->method('_getPostIdFieldName')->willReturn($postIdField);

        $changeset = [
            $f1 = uniqid('field-') => $v1 = uniqid('value-'),
            $f2 = uniqid('field-') => $v2 = uniqid('value-'),
            $f3 = uniqid('field-') => $v3 = uniqid('value-'),
        ];

        $subject->method('_normalizeWpPostDataArray')->willReturnArgument(0);

        $subject->expects($this->never())
                ->method('_wpUpdatePost')
                ->with($changeset);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_update($changeset);
    }

    /**
     * Tests the update method to assert whether an exception is thrown if no ID is found in the given condition.
     *
     * @since [*next-version*]
     */
    public function testUpdateNoPostIdsFromCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $postIdField = uniqid('post-id-');
        $subject->method('_getPostIdFieldName')->willReturn($postIdField);

        $expression = $this->createLogicalExpression(uniqid('type-'), [], false);
        $subject->expects($this->atLeastOnce())
                ->method('_extractPostIdsFromExpression')
                ->with($expression)
                ->willReturn([]);

        // Input array of post data
        $input = [
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
            uniqid('key-') => uniqid('value-'),
        ];

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_update($input, $expression);
    }
}
