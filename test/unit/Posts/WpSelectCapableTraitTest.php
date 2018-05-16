<?php

namespace RebelCode\Storage\Resource\WordPress\Posts\UnitTest;

use Dhii\Expression\LogicalExpressionInterface;
use PHPUnit_Framework_MockObject_MockObject;
use stdClass;
use Xpmock\TestCase;

/**
 * Tests {@see \RebelCode\Storage\Resource\WordPress\WpSelectCapableTrait}.
 *
 * @since [*next-version*]
 */
class WpSelectCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Posts\SelectCapableWpTrait';

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
                                    '_buildWpQueryArgs',
                                    '_filterWpQueryArgs',
                                    '_filterWpQueryPosts',
                                    '_createWpQuery',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();

        return $mock;
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
            'An instance of the test subject could not be created'
        );
    }

    /**
     * Tests the select method to assert whether all involved functionality is invoked and works as expected and that
     * the returned result set is as expected.
     *
     * @since [*next-version*]
     */
    public function testSelect()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createLogicalExpression('', []);
        $args = [
            'post_type' => 'post',
            'post_author' => '16',
        ];
        $expected = [
            548 => [uniqid('test-post-')],
            570 => [uniqid('test-post-')],
        ];
        $subject->expects($this->atLeastOnce())
                ->method('_buildWpQueryArgs')
                ->willReturn($args);
        $subject->expects($this->once())
                ->method('_filterWpQueryArgs')
                ->willReturnArgument(0);
        $subject->expects($this->once())
                ->method('_filterWpQueryPosts')
                ->willReturnArgument(0);
        $subject->expects($this->atLeastOnce())
                ->method('_createWpQuery')->willReturnCallback(
                function ($args) use ($expected) {
                    $obj = new stdClass();
                    $obj->posts = $expected;

                    return $obj;
                }
            );

        $this->assertEquals($expected, $reflect->_select($expression), 'Expected and retrieved results are not equal.');
    }
}
