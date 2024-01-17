<?php

include __DIR__ . "/../src/matrix.php";

use divengine\matrix;

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