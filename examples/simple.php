<?php

include __DIR__ . "/../src/matrix.php";

use divengine\matrix;

// simple list of nums
$nums = new matrix([
    ["", 1, 2, 3],
    ["", 4, 5, 6]
]);

// get item 
echo $nums->get(1, 3); // 6

// set item
$nums->set(1, 3, 10);
echo $nums->formatTXT();
echo PHP_EOL;

// get row
print_r($nums->getRow(1)); // [4, 5, 10]

// get column
print_r($nums->getColumn(3)); // [3, 10]

// get range
$range = $nums->range(0, 0, 1, 1); // [[1, 2], [4, 5]]

// new matrix from range
$rangeMatrix = new matrix($range);

// show $range
echo $rangeMatrix->formatTXT();
echo PHP_EOL;

// add row
$nums->addRow(["", 0, 0, 0]);
echo $nums->formatTXT();
echo PHP_EOL;

// add column
$nums->addColumn(0);
echo $nums->formatTXT();
echo PHP_EOL;

// fill function
$nums->fillVertical(0, 0, $nums->getTotalRows() - 1, fn() => date("Y-m-d"));
echo $nums->formatTXT();
echo PHP_EOL;

// add row on top
$nums->addRow(["Date\t", "Value1", "Value2", "Value3", "Value4"], onTop: true);
echo $nums->formatTXT(true);