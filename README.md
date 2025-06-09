# tomkyle/transposer

[![PHP Version](https://img.shields.io/badge/php-%5E8.0-blue)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![Tests](https://img.shields.io/badge/tests-passing-brightgreen)](tests)

A lightweight, zero-dependency utility class for transposing two-dimensional associative arrays. Converts nested data structures from `[category][field] => value` format into `[field][label => field, category => value]` format, making the data suitable for table display where fields become rows and categories become columns.

## Installation

Install via Composer:

```bash
composer require tomkyle/transposer
```

## Requirements

- PHP 8.3 or higher
- No additional dependencies


## Overview

The IterableTransposer performs matrix-like transposition on associative arrays or iterables. It is particularly useful for transforming data that is grouped by categories into a format that can be easily displayed in tables or exported to formats like CSV.

- **Input**: Nested array with categories containing field-value pairs
- **Output**: Transposed array with fields as keys, each containing category-value pairs
- **Use case**: Converting grouped data into table-ready format

## Basic Usage

The constructor accepts an optional label for the first column of the transposal result, which can be used to describe the fields. If no label is provided, the first column will not have a label. In this example, we will use the label "Metric" for the first column in the transposed array which carries the 1st-level key names (e.g., "revenue", "orders" …).


```php
use tomkyle\Transposer\IterableTransposer;

$transposer = new IterableTransposer('Metric');

$data = [
    'Q1-2023' => [
        'revenue' => 125000,
        'orders' => 1250,
        'customers' => 800
    ],
    'Q2-2023' => [
        'revenue' => 138000,
        'orders' => 1380,
        'customers' => 920
    ],
    'Q3-2023' => [
        'revenue' => 142000,
        'orders' => 1420,
        'customers' => 980
    ]
];

$result = $transposer($data);
```

**Output:**
```php
[
    'revenue' => [
        'Metric' => 'revenue',
        'Q1-2023' => 125000,
        'Q2-2023' => 138000,
        'Q3-2023' => 142000
    ],
    'orders' => [
        'Metric' => 'orders',
        'Q1-2023' => 1250,
        'Q2-2023' => 1380,
        'Q3-2023' => 1420
    ],
    'customers' => [
        'Metric' => 'customers',
        'Q1-2023' => 800,
        'Q2-2023' => 920,
        'Q3-2023' => 980
    ]
]
```

## Constructor Options

The constructor can take an optional label parameter that will be used as the header for the first column in the transposed result. If no label is provided, the first column will not have such a label.

First column will be labeled “Field Name”:
```php
use tomkyle\Transposer\IterableTransposer;
$transposer = new IterableTransposer('Field Name');
$transposer = new IterableTransposer();
```



## Method Parameters

### __invoke($inputArr, $label = null)

- **$inputArr**: Array or iterable containing nested associative data
- **$label**: Optional runtime override for the label column name

```php
$transposer = new IterableTransposer('Default');
$result = $transposer($data, 'Custom Label'); // Uses "Custom Label" instead of "Default"
```



## Integration Examples

```php
$salesData = [
    'January' => ['online' => 50000, 'retail' => 30000, 'wholesale' => 20000],
    'February' => ['online' => 55000, 'retail' => 32000, 'wholesale' => 22000],
    'March' => ['online' => 60000, 'retail' => 35000, 'wholesale' => 25000]
];

$transposer = new IterableTransposer('Channel');
$tableData = $transposer($salesData);
```


After tranposal, the data can be used in various formats such as Markdown tables, Symfony Console tables, or exported to CSV. Its result looks like so:

```text
Array
(
    [online] => Array
        (
            [Channel] => online
            [January] => 50000
            [February] => 55000
            [March] => 60000
        )

    [retail] => Array
        (
            [Channel] => retail
            [January] => 30000
            [February] => 32000
            [March] => 35000
        )

    [wholesale] => Array
        (
            [Channel] => wholesale
            [January] => 20000
            [February] => 22000
            [March] => 25000
        )

)
```


The result as a Markdown table would look like this:


```markdown
| Channel   | January | February | March |
|-----------|---------|----------|-------|
| online    | 50000   | 55000    | 60000 |
| retail    | 30000   | 32000    | 35000 |
| wholesale | 20000   | 22000    | 25000 |
```



### With Symfony Console Tables
```php
use Symfony\Component\Console\Helper\Table;

$transposer = new IterableTransposer('Channel');
$tableData = $transposer($data);

$table = new Table($output);
$table->setHeaders(array_keys(reset($tableData)));

foreach ($tableData as $row) {
    $table->addRow($row);
}
$table->render();
```

### CSV Export
```php
$transposer = new IterableTransposer('Channel');
$csvData = $transposer($data);

$fp = fopen('export.csv', 'w');
// Write headers first
fputcsv($fp, array_keys(reset($csvData)));
// Write data rows
foreach ($csvData as $row) {
    fputcsv($fp, $row);
}
fclose($fp);
```

## Error Handling

The IterableTransposer is designed to be robust and handles edge cases gracefully:

```php
$transposer = new IterableTransposer('Field');

// Empty input
$result = $transposer([]); // Returns []

// Non-nested data
$result = $transposer(['a', 'b', 'c']); // Returns []

// Mixed data types
$data = [
    'category1' => ['field1' => 'value1', 'field2' => 123],
    'category2' => ['field1' => null, 'field2' => true]
];
$result = $transposer($data); // Handles mixed types properly
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test:coverage
```

Run static analysis:

```bash
composer analyse
```

## Development

This project follows PSR-12 coding standards and includes:

- PHPUnit for testing
- PHPStan for static analysis  
- PHP-CS-Fixer for code formatting
- Rector for automated refactoring
- File watching for continuous development

### Setup Development Environment

```bash
git clone https://github.com/tomkyle/transposer.git
cd iterable-transposer
composer install
npm install
```

### Development Workflow

The project uses npm scripts for development tasks:

```bash
# Watch files for changes (runs PHPStan, Rector, and tests automatically)
npm run watch

# Code quality tools
npm run phpcs          # Check code style (dry-run)
npm run phpcs:apply    # Fix code style

npm run phpstan        # Run static analysis

npm run rector         # Check for refactoring suggestions (dry-run)
npm run rector:apply   # Apply refactoring suggestions

# Testing
npm run phpunit        # Run tests with coverage
npm run phpunit:short  # Run tests without coverage
```

### File Watching

The watch command monitors source and test files for changes and automatically runs the appropriate tools:

- **Source files** (`src/**/*.php`): Runs PHP-CS-Fixer, PHPStan, and Rector
- **Test files** (`tests/**/*.php`): Runs PHPUnit tests

## Performance Considerations

- **Memory**: Creates a new array structure; original data remains unchanged
- **Time Complexity**: $O(n \times m)$ where $n$ = categories, $m$ = fields per category
- **Best For**: Small to medium datasets (thousands of records)
- **Large Data**: Consider streaming for very large datasets

## API Reference

### IterableTransposer::__construct(?string $label = null)
Creates a new transposer instance with an optional default label.

### IterableTransposer::__invoke(iterable $inputArr, ?string $label = null): array
Transposes the input array and returns the result.

**Parameters:**
- `$inputArr`: Nested associative array or iterable
- `$label`: Optional label override for the first column

**Returns:** Transposed associative array

**Throws:** No exceptions; returns empty array for invalid input

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request. For major changes, please open an issue first to discuss what you would like to change.

### Guidelines

- Follow PSR-12 coding standards
- Add tests for any new features
- Update documentation as needed
- Ensure all tests pass

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Changelog

All notable changes to this project will be documented in the [CHANGELOG.md](CHANGELOG.md) file.

## Support

- [GitHub Issues](https://github.com/tomkyle/iterable-transposer/issues) for bug reports and feature requests
- [GitHub Discussions](https://github.com/tomkyle/iterable-transposer/discussions) for questions and community support

