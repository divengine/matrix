# Div PHP Matrix 1.0

A versatile utility for efficient manipulation of matrix data, providing methods for adding and removing rows, validating matrix integrity, and formatting data in various output formats such as CSV, XML, JSON, HTML, Markdown, TXT, YAML and SQL. Simplify matrix-related tasks with this comprehensive utility designed for seamless integration into diverse applications.

## Features

- Controlled matrix data manipulation
- Validate matrix integrity
- Format data in various output formats
- Simplify matrix-related tasks
- Spreadsheet-like functionality

## Requirements

- PHP 8.0 or higher

## Installation

```shell
composer require divengine/matrix
```

## Basic usage

```php

use divengine\matrix;

$table = new matrix([
    ["Product", "Price", "Count"],
    ["Apple", 10, 2],
    ["Banana", 35, 3],
    ["Orange", 6, 10],
]);

echo $table->format('txt', true);

$table->addColumn();
$table->setValue(0, 3, "Amount");
$table->fillVertical(3, 1, 3, 
    fn ($row, $col, matrix $matrix) 
        => $matrix->getValue($row, $col - 1) * $matrix->getValue($row, $col - 2));
$table->addRow(["Total", "", "", 
    fn ($row, $col, matrix $matrix) 
        => array_sum($matrix->getVertical($col, 1, $row - 1))]);
echo $table->format('txt', true);

$table->addColumn();
$table->setValue(0, 4, "Cumul");
$table->fillVertical(4, 1, 3, 
    fn ($row, $col, matrix $matrix) 
        => $row == 1 
            ? $matrix->getValue($row, $col - 1) 
            : $matrix->getValue($row - 1, $col) + $matrix->getValue($row, $col - 1));
echo $table->format('txt', true);

$table->setValue(1, 1, 20);
echo $table->format('txt', true);

$table->removeRow(1);
echo $table->format('txt', true);

print_r($table->getVertical(1, 1, 2));
print_r($table->getHorizontal(1, 1, 2));
```

Ouput:

```shell
Product Price   Count
Apple   10      2
Banana  35      3
Orange  6       10

Product Price   Count   Amount
Apple   10      2       20
Banana  35      3       105
Orange  6       10      60
Total                   185

Product Price   Count   Amount  Cumul
Apple   10      2       20      20
Banana  35      3       105     125
Orange  6       10      60      185
Total                   185

Product Price   Count   Amount  Cumul
Apple   20      2       40      40
Banana  35      3       105     145
Orange  6       10      60      205
Total                   205

Product Price   Count   Amount  Cumul
Banana  35      3       105     105
Orange  6       10      60      165
Total                   165

Array
(
    [0] => 35
    [1] => 6
)

Array
(
    [0] => 35
    [1] => 3
)
    
```

