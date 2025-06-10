<?php

/**
 * This file is part of tomkyle/transposer
 *
 * A PHP library for transposing arrays and objects.
 */

namespace tomkyle\Transposer;

/**
 * Transposes two-dimensional associative arrays.
 *
 * Converts nested associative arrays from [outerKey][innerKey] => value format
 * to [innerKey][labelColumn => innerKey, outerKey => value] format, effectively
 * transposing the data structure for table display.
 *
 * Input format:
 *    Array(
 *       [Q1-2023] => Array(
 *         [revenue] => 125000
 *         [orders] => 1250
 *       )
 *       [Q2-2023] => Array(
 *         [revenue] => 138000
 *         [orders] => 1380
 *       )
 *     )
 *
 * Output format:
 *     Array (
 *       [revenue] => Array (
 *         [Metric] => revenue
 *         [Q1-2023] => 125000
 *         [Q2-2023] => 138000
 *       )
 *       [orders] => Array (
 *         [Metric] => orders
 *         [Q1-2023] => 1250
 *         [Q2-2023] => 1380
 *       )
 *     )
 */
class IterableTransposer
{
    /**
     * Optional label for the first column header
     */
    public function __construct(
        private readonly ?string $label = null,
    ) {}

    /**
     * Transposes a two-dimensional associative array.
     *
     * Takes nested data where fields are grouped under categories and converts
     * it to a structure where each field becomes a row with categories as columns.
     * Empty or invalid input data returns an empty array.
     *
     * @param iterable<string,array<string,mixed>> $inputArr The nested associative array to transpose
     * @param string|null $label Optional label for the first column header
     * @return array<string, array<string, mixed>> Transposed array with fields as keys
     */
    public function __invoke(iterable $inputArr, ?string $label = null): array
    {
        $inputArr = $inputArr instanceof \Traversable ? iterator_to_array($inputArr) : $inputArr;

        if ($inputArr === []) {
            return [];
        }

        // Ensure the first key is a string and has valid data
        $firstKey = array_key_first($inputArr);

        $fields = array_keys($inputArr[$firstKey]);

        return array_combine($fields, array_map(
            fn(string $field) => $this->extractRow($field, $inputArr, $label),
            $fields,
        ));
    }

    /**
     * Extracts a single field row from the nested data.
     *
     * Creates a row where the first column contains the field name (with optional label)
     * and subsequent columns contain the field values for each category. Missing values
     * are automatically filled with 0.
     *
     * @param string $field The field name to extract
     * @param array<string,array<string,mixed>> $inputArr The source nested data
     * @param string|null $label Optional label for the first column
     * @return array<string, mixed> Associative array representing a table row
     */
    private function extractRow(string $field, array $inputArr, ?string $label): array
    {
        $label ??= $this->label;

        $row = $label ? [$label => $field] : [];

        foreach ($inputArr as $category => $data) {
            $row[$category] = $data[$field] ?? null;
        }

        return $row;
    }


}
