# Div PHP Matrix 1.0

A versatile utility for efficient manipulation of matrix data, providing methods for adding and removing rows, validating matrix integrity, and formatting data in various output formats such as CSV, XML, JSON, HTML, Markdown, TXT, YAML and SQL. Simplify matrix-related tasks with this comprehensive utility designed for seamless integration into diverse applications.

## Features

- Spreadsheet-like functionality
- Controlled matrix data manipulation
- Validate matrix integrity
- Format data in various output formats
- Simplify matrix-related tasks

## Requirements

- PHP 8.0 or higher

## Installation

```shell
composer require divengine/matrix
```

## Basic usage

```php
<?php

// Create a matrix
$table = new matrix([
    ["Product", "Price", "Count"],
    ["Apple", 10, 2],
    ["Banana", 35, 3],
    ["Orange", 6, 10],
]);

// Show the matrix
echo $table->format('txt', true);

$table->addColumn();
$table->set(0, 3, "Amount");

// Fill the column with the product of the previous two columns
$table->fillVertical(3, 1, 3, 
    fn ($r, $c, matrix $m) 
        => $m->get($r, $c - 1) * $m->get($r, $c - 2));

// Add a row with the sum of the previous rows
$table->addRow(["Total", "", "", 
    fn ($r, $c, matrix $m) 
        => array_sum($m->vertical($c, 1, $r - 1))]);
echo $table->format('txt', true);

// Add a column with the sum of the previous columns
$table->addColumn();
$table->set(0, 4, "Cumul");

// Fill the column with the sum of the previous column
$table->fillVertical(4, 1, 3, 
    fn ($r, $c, matrix $m) 
        => $r == 1 ? $m->get($r, $c - 1) 
        : $m->get($r - 1, $c) + $m->get($r, $c - 1));

echo $table->format('txt', true);

// Change a value of the second row
$table->set(1, 1, 20);
echo $table->format('txt', true);

// Remove the second row
$table->removeRow(1);
echo $table->format('txt', true);

// Show ranges
print_r($table->vertical(1, 1, 2));
print_r($table->horizontal(1, 1, 2));

// Serialize
echo $table->format('serialize');
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

