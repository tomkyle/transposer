<?php

/**
 * This file is part of tomkyle/transposer
 *
 * A PHP library for transposing arrays and objects.
 */

namespace tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use tomkyle\Transposer\IterableTransposer;

#[CoversClass(IterableTransposer::class)]
class IterableTransposerTest extends TestCase
{
    #[Test]
    public function constructorWithoutLabelCreatesInstance(): void
    {
        $transposer = new IterableTransposer();
        $this->assertInstanceOf(IterableTransposer::class, $transposer);
    }


    #[Test]
    public function constructorWithLabelCreatesInstance(): void
    {
        $transposer = new IterableTransposer('Metric');
        $this->assertInstanceOf(IterableTransposer::class, $transposer);
    }

    #[Test]
    public function emptyArrayReturnsEmptyArray(): void
    {
        $transposer = new IterableTransposer();
        $result = $transposer([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }


    #[Test]
    public function emptyNestedArrayReturnsEmptyArray(): void
    {
        $transposer = new IterableTransposer();
        $result = $transposer(['category1' => []]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function basicTranspositionWorksCorrectly(): void
    {
        $data = [
            'Q1-2023' => [
                'revenue' => 125000,
                'orders' => 1250,
            ],
            'Q2-2023' => [
                'revenue' => 138000,
                'orders' => 1380,
            ],
        ];

        $transposer = new IterableTransposer('Metric');
        $result = $transposer($data);

        $expected = [
            'revenue' => [
                'Metric' => 'revenue',
                'Q1-2023' => 125000,
                'Q2-2023' => 138000,
            ],
            'orders' => [
                'Metric' => 'orders',
                'Q1-2023' => 1250,
                'Q2-2023' => 1380,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function transpositionWithoutLabelWorksCorrectly(): void
    {
        $data = [
            'Q1-2023' => [
                'revenue' => 125000,
                'orders' => 1250,
            ],
            'Q2-2023' => [
                'revenue' => 138000,
                'orders' => 1380,
            ],
        ];

        $transposer = new IterableTransposer();
        $result = $transposer($data);

        $expected = [
            'revenue' => [
                'Q1-2023' => 125000,
                'Q2-2023' => 138000,
            ],
            'orders' => [
                'Q1-2023' => 1250,
                'Q2-2023' => 1380,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function runtimeLabelOverridesConstructorLabel(): void
    {
        $data = [
            'Q1-2023' => ['revenue' => 125000],
            'Q2-2023' => ['revenue' => 138000],
        ];

        $transposer = new IterableTransposer('Default');
        $result = $transposer($data, 'Custom');

        $expected = [
            'revenue' => [
                'Custom' => 'revenue',
                'Q1-2023' => 125000,
                'Q2-2023' => 138000,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function missingFieldsAreFilledWithNull(): void
    {
        $data = [
            'A' => ['x' => 1, 'y' => 2],
            'B' => ['x' => 3], // 'y' missing
        ];

        $transposer = new IterableTransposer('Field');
        $result = $transposer($data);

        $expected = [
            'x' => [
                'Field' => 'x',
                'A' => 1,
                'B' => 3,
            ],
            'y' => [
                'Field' => 'y',
                'A' => 2,
                'B' => null, // Missing 'y' in 'B'
            ],
        ];
        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function arrayObjectInputIsHandledCorrectly(): void
    {
        $data = new \ArrayObject([
            'Q1-2023' => ['revenue' => 125000],
            'Q2-2023' => ['revenue' => 138000],
        ]);

        $transposer = new IterableTransposer('Metric');
        $result = $transposer($data);

        $expected = [
            'revenue' => [
                'Metric' => 'revenue',
                'Q1-2023' => 125000,
                'Q2-2023' => 138000,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function iteratorInputIsHandledCorrectly(): void
    {
        $data = new \ArrayIterator([
            'Q1-2023' => ['revenue' => 125000],
            'Q2-2023' => ['revenue' => 138000],
        ]);

        $transposer = new IterableTransposer('Metric');
        $result = $transposer($data);

        $expected = [
            'revenue' => [
                'Metric' => 'revenue',
                'Q1-2023' => 125000,
                'Q2-2023' => 138000,
            ],
        ];

        $this->assertEquals($expected, $result);
    }

    #[Test]
    public function multipleFieldsWithMixedDataTypes(): void
    {
        $data = [
            'Category1' => [
                'string_field' => 'value1',
                'int_field' => 100,
                'float_field' => 99.9,
                'bool_field' => true,
                'null_field' => null,
            ],
            'Category2' => [
                'string_field' => 'value2',
                'int_field' => 200,
                'float_field' => 199.8,
                'bool_field' => false,
                'null_field' => null,
            ],
        ];

        $transposer = new IterableTransposer('Type');
        $result = $transposer($data);

        $this->assertCount(5, $result);
        $this->assertEquals('value1', $result['string_field']['Category1']);
        $this->assertEquals(100, $result['int_field']['Category1']);
        $this->assertEquals(99.9, $result['float_field']['Category1']);
        $this->assertTrue($result['bool_field']['Category1']);
        $this->assertNull($result['null_field']['Category1']);
    }

    #[Test]
    public function largeDatasetPerformance(): void
    {
        // Generate test data with 100 categories and 50 fields each
        $data = [];
        for ($i = 1; $i <= 100; $i++) {
            $category = 'Category' . $i;
            $data[$category] = [];
            for ($j = 1; $j <= 50; $j++) {
                $data[$category]['field' . $j] = random_int(1, 1000);
            }
        }

        $transposer = new IterableTransposer('Field');

        $startTime = microtime(true);
        $result = $transposer($data);
        $endTime = microtime(true);

        // Should complete within reasonable time (< 1 second)
        $this->assertLessThan(1.0, $endTime - $startTime);
        $this->assertCount(50, $result);
        $this->assertCount(101, $result['field1']); // 100 categories + 1 label
    }

    #[Test]
    public function preservesFieldOrderFromFirstCategory(): void
    {
        $data = [
            'Cat1' => [
                'z_field' => 1,
                'a_field' => 2,
                'm_field' => 3,
            ],
            'Cat2' => [
                'z_field' => 4,
                'a_field' => 5,
                'm_field' => 6,
            ],
        ];

        $transposer = new IterableTransposer();
        $result = $transposer($data);

        // Field order should match the order in the first category
        $expectedOrder = ['z_field', 'a_field', 'm_field'];
        $this->assertEquals($expectedOrder, array_keys($result));
    }

    #[Test]
    public function nonStringKeysAreHandledGracefully(): void
    {
        $data = [
            2 => ['field1' => 'value1'],
            'string_key' => ['field1' => 'value2'],
        ];

        $transposer = new IterableTransposer('Label');
        $result = $transposer($data);


        // Should handle mixed key types
        $this->assertIsArray($result);
        $this->assertArrayHasKey('field1', $result);
        $this->assertArrayHasKey(2, $result['field1']);
        $this->assertArrayHasKey('string_key', $result['field1']);
    }
}
