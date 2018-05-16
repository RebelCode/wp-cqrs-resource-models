<?php

namespace RebelCode\Storage\Resource\WordPress\Posts\FuncTest;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\TermInterface;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_MockBuilder as MockBuilder;
use RebelCode\Storage\Resource\WordPress\Posts\SearchExpressionTermsCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class SearchExpressionTermsCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Posts\SearchExpressionTermsCapableTrait';

    /**
     * Creates a new instance of the test subject.
     *
     * @since [*next-version*]
     *
     * @param array $methods Optional additional mock methods.
     *
     * @return MockObject|TestSubject
     */
    public function createInstance(array $methods = [])
    {
        $builder = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                        ->setMethods(
                            array_merge(
                                $methods,
                                [
                                    '_mapIterable',
                                ]
                            )
                        );

        $mock = $builder->getMockForTrait();

        return $mock;
    }

    /**
     * Creates a mock that both extends a class and implements interfaces.
     *
     * This is particularly useful for cases where the mock is based on an
     * internal class, such as in the case with exceptions. Helps to avoid
     * writing hard-coded stubs.
     *
     * @since [*next-version*]
     *
     * @param string   $className      Name of the class for the mock to extend.
     * @param string[] $interfaceNames Names of the interfaces for the mock to implement.
     *
     * @return MockBuilder The object that extends and implements the specified class and interfaces.
     */
    public function mockClassAndInterfaces($className, $interfaceNames = [])
    {
        $paddingClassName = uniqid($className);
        $definition = vsprintf(
            'abstract class %1$s extends %2$s implements %3$s {}',
            [
                $paddingClassName,
                $className,
                implode(', ', $interfaceNames),
            ]
        );
        eval($definition);

        return $this->getMockBuilder($paddingClassName);
    }

    /**
     * Creates a new mock expression instance.
     *
     * @since [*next-version*]
     *
     * @param string $type  The expression type.
     * @param array  $terms The expression terms.
     *
     * @return ExpressionInterface
     */
    public function createExpression($type = '', $terms = [])
    {
        return $this->mock('Dhii\Expression\ExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
                    ->new();
    }

    /**
     * Creates a new mock term instance.
     *
     * @since [*next-version*]
     *
     * @param string $type The term type.
     *
     * @return TermInterface
     */
    public function createTerm($type = '')
    {
        return $this->mock('Dhii\Expression\TermInterface')
                    ->getType($type)
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
     * Tests the expression terms search method to assert whether the results of the internal iterable mapping method
     * are correctly returned.
     *
     * @since [*next-version*]
     */
    public function testSearchExpressionTerms()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createExpression(
            '',
            $terms = [
                $term1 = $this->createTerm(),
                $term2 = $this->createTerm(),
                $term3 = $this->createTerm(),
                $term4 = $this->createTerm(),
            ]
        );
        $expected = [$term2, $term3];
        $callback = function () {
        };
        $count = rand(0, 4);

        $subject->expects($this->once())
                ->method('_mapIterable')
                ->with($expression->getTerms(), $callback, null, $count, $this->anything())
                ->willReturnCallback(
                    function ($iterable, $callback, $start, $count, &$results) use ($expected) {
                        $results = $expected;
                    }
                );

        $this->assertEquals(
            $expected,
            $reflect->_searchExpressionTerms($expression, $callback, $count),
            'Expected and retrieved results do not match.'
        );
    }

    /**
     * Tests the expression terms search method to assert whether an InvalidArgumentException is thrown when the
     * internal iterable mapper method throws an InvalidArgumentException.
     *
     * @since [*next-version*]
     */
    public function testSearchExpressionTermsInvalidArgumentException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createExpression(
            '',
            $terms = [
                $this->createTerm(),
                $this->createTerm(),
                $this->createTerm(),
            ]
        );
        $callback = function () {
        };
        $count = uniqid('invalid-count-');

        $subject->expects($this->once())
                ->method('_mapIterable')
                ->with($expression->getTerms(), $callback, null, $count, $this->anything())
                ->willThrowException(new InvalidArgumentException());

        $this->setExpectedException('InvalidArgumentException');

        $reflect->_searchExpressionTerms($expression, $callback, $count);
    }

    /**
     * Tests the expression terms search method to assert whether an InvocationException is thrown when the internal
     * iterable mapper method throws an InvocationException.
     *
     * @since [*next-version*]
     */
    public function testSearchExpressionTermsInvocationException()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $expression = $this->createExpression(
            '',
            $terms = [
                $this->createTerm(),
                $this->createTerm(),
                $this->createTerm(),
            ]
        );
        $callback = function () {
        };
        $count = uniqid('invalid-count-');

        $invocationExceptionInterface = 'Dhii\Invocation\Exception\InvocationExceptionInterface';
        $exception = $this->mockClassAndInterfaces('Exception', [$invocationExceptionInterface])
                          ->enableProxyingToOriginalMethods()
                          ->setMethods(['getCallable', 'getArgs'])
                          ->getMockForAbstractClass();

        $subject->expects($this->once())
                ->method('_mapIterable')
                ->with($expression->getTerms(), $callback, null, $count, $this->anything())
                ->willThrowException($exception);

        $this->setExpectedException($invocationExceptionInterface);

        $reflect->_searchExpressionTerms($expression, $callback, $count);
    }
}
