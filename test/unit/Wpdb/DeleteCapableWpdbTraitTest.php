<?php

namespace RebelCode\Storage\Resource\WordPress\Wpdb\UnitTest;

use Dhii\Expression\LogicalExpressionInterface;
use RebelCode\Storage\Resource\WordPress\Wpdb\DeleteCapableWpdbTrait as TestSubject;
use Xpmock\TestCase;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

/**
 * Tests {@see TestSubject}.
 *
 * @since [*next-version*]
 */
class DeleteCapableWpdbTraitTest extends TestCase
{
    /**
     * The class name of the test subject.
     *
     * @since [*next-version*]
     */
    const TEST_SUBJECT_CLASSNAME = 'RebelCode\Storage\Resource\WordPress\Wpdb\DeleteCapableWpdbTrait';

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
                '_buildDeleteSql',
                '_getSqlDeleteTable',
                '_getSqlDeleteFieldNames',
                '_getWpdbExpressionHashMap',
                '_executeWpdbQuery',
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
     * Tests the delete method to assert whether the hash map and query are correctly generated and executed.
     *
     * @since [*next-version*]
     */
    public function testDelete()
    {
        $subject = $this->createInstance();
        $reflect = $this->reflect($subject);

        $condition = $this->createLogicalExpression('', []);
        $table = uniqid('table-');
        $fields = [
            uniqid('field-'),
            uniqid('field-'),
            uniqid('field-'),
            uniqid('field-'),
        ];
        $subject->method('_getSqlDeleteTable')->willReturn($table);
        $subject->method('_getSqlDeleteFieldNames')->willReturn($fields);

        $hashMap = [
            uniqid('value-') => uniqid('hash-'),
            uniqid('value-') => uniqid('hash-'),
            uniqid('value-') => uniqid('hash-'),
        ];
        $subject->method('_getWpdbExpressionHashMap')->willReturn($hashMap);

        $query = uniqid('query-');
        $subject->expects($this->atLeastOnce())
                ->method('_buildDeleteSql')
                ->with($table, $condition, $hashMap)
                ->willReturn($query);

        $subject->expects($this->once())
                ->method('_executeWpdbQuery')
                ->with($query, array_flip($hashMap));

        $reflect->_delete($condition);
    }
}
