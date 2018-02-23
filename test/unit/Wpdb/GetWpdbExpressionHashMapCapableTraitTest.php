<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb\UnitTest;

use Dhii\Expression\ExpressionInterface;
use Dhii\Expression\LiteralTermInterface;
use Dhii\Expression\TermInterface;
use RebelCode\Storage\Resource\WordPress\Wpdb\GetWpdbExpressionHashMapCapableTrait as TestSubject;
use Xpmock\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class GetWpdbExpressionHashMapCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Wpdb\GetWpdbExpressionHashMapCapableTrait';

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
                '_getWpdbValueHashString',
                '_normalizeString',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('_normalizeString')->willReturnCallback(
            function($s) {
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
     * Creates an expression mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type  The expression type.
     * @param array  $terms The expression terms.
     *
     * @return ExpressionInterface The created expression instance.
     */
    public function createExpression($type, $terms)
    {
        return $this->mock('Dhii\Expression\ExpressionInterface')
                    ->getType($type)
                    ->getTerms($terms)
                    ->new();
    }

    /**
     * Creates an expression term mock instance.
     *
     * @since [*next-version*]
     *
     * @param string $type The term type.
     *
     * @return TermInterface The created expression term instance.
     */
    public function createTerm($type)
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
     * @param mixed $value The term value.
     *
     * @return LiteralTermInterface The created literal term instance.
     */
    public function createLiteralTerm($value)
    {
        return $this->mock('Dhii\Expression\LiteralTermInterface')
                    ->getType('literal')
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
     * Tests the WPDB expression hash map method to assert whether the retrieved hash map correctly reflects the
     * literal terms in a given expression, with correct positions and ignoring a given list of values.
     *
     * @since [*next-version*]
     */
    public function testGetWpdbExpressionHashMap()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $literal1 = uniqid('literal-');
        $literal2 = uniqid('literal-');
        $literal3 = uniqid('literal-');
        $ignored1 = uniqid('ignored-');
        $ignored2 = uniqid('ignored-');

        $expression = $this->createExpression(
            uniqid('type-'),
            [
                $this->createLiteralTerm($literal1),
                $this->createExpression(
                    uniqid('type-'),
                    [
                        $this->createLiteralTerm($literal3),
                        $this->createLiteralTerm($ignored1),
                    ]
                ),
                $this->createLiteralTerm($ignored2),
                $this->createExpression(
                    uniqid('type-'),
                    [
                        $this->createLiteralTerm($literal2),
                    ]
                ),
            ]
        );

        $hash = uniqid('hash-');
        $subject->expects($this->atLeast(3))
                ->method('_getWpdbValueHashString')
                ->withConsecutive([$literal1, 1], [$literal3, 2], [$literal2, 3])
                ->willReturn($hash);

        $expected = [
            $literal1 => $hash,
            $literal3 => $hash,
            $literal2 => $hash,
        ];

        $actual = $reflect->_getWpdbExpressionHashMap($expression, [$ignored1, $ignored2]);

        $this->assertEquals($expected, $actual, 'Expected and retrieved value hash maps do not match.');
    }
}
