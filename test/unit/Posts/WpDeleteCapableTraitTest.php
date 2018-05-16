<?php

namespace RebelCode\Storage\Resource\WordPress\Posts\UnitTest;

use Dhii\Expression\LogicalExpressionInterface;
use InvalidArgumentException;
use RebelCode\Storage\Resource\WordPress\Posts\DeleteCapableWpTrait as TestSubject;
use Xpmock\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class WpDeleteCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Posts\DeleteCapableWpTrait';

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
                '_extractPostIdsFromExpression',
                '_wpDeletePost',
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
     * Tests the delete method to assert whether the IDs that are extracted from the expression are used for deletion.
     *
     * @since [*next-version*]
     */
    public function testDelete()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $postIds = [
            $postId1 = rand(0, 500),
            $postId2 = rand(0, 500),
            $postId3 = rand(0, 500),
        ];
        $expression = $this->createLogicalExpression(uniqid('type-'), [], false);

        $subject->expects($this->atLeastOnce())
                ->method('_extractPostIdsFromExpression')
                ->with($expression)
                ->willReturn($postIds);

        $subject->expects($this->exactly(count($postIds)))
                ->method('_wpDeletePost')
                ->withConsecutive([$postId1], [$postId2], [$postId3]);

        $reflect->_delete($expression);
    }

    /**
     * Tests the delete method to assert whether an exception is thrown if no condition is given.
     *
     * @since [*next-version*]
     */
    public function testDeleteNoCondition()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_delete();
    }
}
