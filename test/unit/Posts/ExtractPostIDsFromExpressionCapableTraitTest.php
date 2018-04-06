<?php

namespace RebelCode\Storage\Resource\WordPress\Posts\UnitTest;

use Dhii\Expression\LiteralTermInterface;
use Dhii\Expression\LogicalExpressionInterface;
use Dhii\Expression\Type\BooleanTypeInterface;
use Dhii\Expression\Type\RelationalTypeInterface;
use Dhii\Storage\Resource\Sql\EntityFieldInterface;
use Dhii\Storage\Resource\Sql\Expression\SqlRelationalTypeInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use RebelCode\Storage\Resource\WordPress\Posts\ExtractPostIDsFromExpressionCapableTrait as TestSubject;
use Xpmock\TestCase;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class ExtractPostIDsFromExpressionCapableTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Posts\ExtractPostIdsFromExpressionCapableTrait';

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
                '__',
                '_createInvalidArgumentException',
                '_normalizeInt',
                '_getPostIdFieldName',
            ]
        );

        $mock = $this->getMockBuilder(static::TEST_SUBJECT_CLASSNAME)
                     ->setMethods($methods)
                     ->getMockForTrait();

        $mock->method('__')->will($this->returnArgument(0));
        $mock->method('_normalizeArray')->will($this->returnArgument(0));
        $mock->method('_normalizeInt')->willReturnCallback(
            function ($s) {
                return intval($s);
            }
        );
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
     * Creates an entity field mock instance.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $entity The entity name.
     * @param string|Stringable $field  the field name.
     *
     * @return EntityFieldInterface
     */
    public function createEntityField($entity, $field)
    {
        return $this->mock('Dhii\Storage\Resource\Sql\EntityFieldInterface')
                    ->getEntity($entity)
                    ->getField($field)
                    ->new();
    }

    /**
     * Creates a literal term mock instance.
     *
     * @since [*next-version*]
     *
     * @param mixed $value The value.
     *
     * @return LiteralTermInterface The created term instance.
     */
    public function createLiteralTerm($value)
    {
        return $this->mock('Dhii\Expression\LiteralTermInterface')
                    ->getType()
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
     * Tests the post ID extraction method to assert whether it correctly extracts the post IDs from all of the
     * supported expression types.
     *
     * @since [*next-version*]
     */
    public function testExtractPostIdsFromExpression()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $entity = 'post';
        $field = 'id';

        $subject->method('_getPostEntityName')->willReturn($entity);
        $subject->method('_getPostIdFieldName')->willReturn($field);

        $ids = [
            $id1 = rand(1, 500),
            $id2 = rand(1, 500),
            $id3 = rand(1, 500),
            $id4 = rand(1, 500),
            // range
            $id5 = rand(1, 500),
            $id6 = $id5 + 1,
            $id7 = $id5 + 2,
            $id8 = $id5 + 3,
            $id9 = $id5 + 4,
        ];

        $expression = $this->createLogicalExpression(
            BooleanTypeInterface::T_OR,
            [
                // Simple equivalence: post.id == $id1
                $this->createLogicalExpression(
                    RelationalTypeInterface::T_EQUAL_TO,
                    [
                        $this->createEntityField($entity, $field),
                        $this->createLiteralTerm($id1),
                    ]
                ),
                $this->createLogicalExpression(
                    BooleanTypeInterface::T_OR,
                    [
                        // IN expression: post.id IN ($id2, $id3, $id4)
                        $this->createLogicalExpression(
                            SqlRelationalTypeInterface::T_IN,
                            [
                                $this->createLiteralTerm([$id2, $id3, $id4]),
                                $this->createEntityField($entity, $field),
                            ]
                        ),
                        // BETWEEN expression: post.id BETWEEN ($id5, $id9)
                        $this->createLogicalExpression(
                            SqlRelationalTypeInterface::T_BETWEEN,
                            [
                                $this->createLiteralTerm($id5),
                                $this->createLiteralTerm($id9),
                                $this->createEntityField($entity, $field),
                            ]
                        ),
                    ]
                ),
            ]
        );

        $result = $reflect->_extractPostIdsFromExpression($expression);

        $this->assertEquals($ids, $result);
    }
}
