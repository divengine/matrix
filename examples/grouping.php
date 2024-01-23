<?php

include __DIR__ . "/../src/matrix.php";

use divengine\matrix;

// Create a matrix

$table = new matrix([
    ["Product", "Count"], 
    //------------------
    ["Apple",         2],
    ["Banana",        3],
    ["Banana",        5],
    ["Orange",        6],
    ["Orange",        5],
    ["Orange",        2],
    ["Orange",        3],
]);

// Show the matrix
echo $table;

// Group by
$result = $table->groupBy([0], function($key, $group){
    $sum = 0;
    foreach($group as $row){
        $sum += $row[1];
    }
    return [$key, $sum];
}, true);

echo "\n";
$groupBy = new matrix(array_values($result));
$groupBy->addRow(["Product", "Total"], onTop: true);
echo $groupBy;

