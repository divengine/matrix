<?php

include __DIR__ . "/../src/matrix.php";

use divengine\matrix;

$F_AMOUNT = fn($r, $c, matrix $m) => $m->{$r + .1} * $m->{$r + .2};
$F_TOTAL = fn ($r, $c, matrix $m) => array_sum($m->vertical($c, 1, $r - 1));
$F_CUMUL = fn ($r, $c, matrix $m) => $r == 1 ? $m->get($r, $c - 1) 
                                             : $m->get($r - 1, $c) + $m->get($r, $c - 1);
$table = new matrix([
    ["Product", "Price", "Count"],
    ["Apple", 10, 2],
    ["Banana", 35, 3],
    ["Orange", 6, 10],
]);

echo $table . "\n";

$table->addColumn();
$table->{0.3} = "Amount";

// Fill the column with the product of the previous two columns
$table->fillVertical(3, 1, 3, $F_AMOUNT);

// Add a row with the sum of the previous rows
$table->addRow(["Total", "", "", $F_TOTAL]);
echo $table . "\n";

// Add a column with the sum of the previous columns
$table->addColumn();
$table->{0.4} = "Cumul";

// Fill the column with the sum of the previous column
$table->fillVertical(4, 1, 3, $F_CUMUL);
echo $table . "\n";

// Change a value of the second row
$table->{1.1} = 20;
echo $table . "\n";

// Remove the second row
$table->removeRow(1);
echo $table . "\n";

// Show ranges
print_r($table->vertical(1, 1, 2));
print_r($table->horizontal(1, 1, 2));
print_r($table->range(1, 1, 2, 2));
echo "\n";

// Serialize
echo $table->format('serialize');