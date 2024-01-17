<?php

include __DIR__ . "/../src/matrix.php";

use divengine\matrix;

$F_AMOUNT = fn($row, $col, $matrix) => $matrix->get($row, $col - 2) * $matrix->get($row, $col - 1);
$F_TOTAL = fn($row, $col, $matrix) => array_sum($matrix->vertical($col, 1, $row - 1));
$F_AVG = fn($row, $col, $matrix) => number_format($F_TOTAL($row, $col, $matrix) / ($row - 1), 1);
$F_BOTTOM_RIGHT = fn(matrix $matrix) => $matrix->get($matrix->getTotalRows() - 1, $matrix->getTotalColumns() - 1);

// sheet of products
$products = new matrix([
            /*  0         1         2           3    */
    /* 0 */ ["Product", "Price",  "Count",   "Amount"],
            //----------------------------------------
    /* 1 */ ["Apple",         5,        2,  $F_AMOUNT],
    /* 2 */ ["Banana",        6,        3,  $F_AMOUNT],
    /* 3 */ ["Orange",        6,       10,  $F_AMOUNT],
            //-----------------------------------------
    /* 4 */ ["Totals",  $F_AVG,  $F_TOTAL,   $F_TOTAL]
]);

// sheet of services
$services = new matrix([
           /*  0          1         2           3    */
   /* 0 */ ["Service",  "Price",  "Count",    "Amount"],
           //-----------------------------------------
   /* 1 */ ["Clean",         10,        2,   $F_AMOUNT],
   /* 2 */ ["Paint",         35,        3,   $F_AMOUNT],
   /* 3 */ ["Repair",         6,       10,   $F_AMOUNT],
           //------------------------------------------
   /* 4 */ ["Totals", $F_AVG,   $F_TOTAL,     $F_TOTAL]
]);

$T_PRODUCTS = fn() => $F_BOTTOM_RIGHT($products);
$T_SERVICES = fn() => $F_BOTTOM_RIGHT($services);

// sheet of earnings
$earnings = new matrix([
    ["\t",           "Amount"],
    //------------------------
    ["Products",  $T_PRODUCTS],
    ["Services",  $T_SERVICES],
    //------------------------
    ["Earnings",     $F_TOTAL]
]);

echo $products->formatTXT(true);
echo "\n";
echo $services->formatTXT(true);
echo "\n";
echo $earnings->formatTXT(true);
echo "\n";

$products->set(1, 1, 10); // $earnings will be updated automatically

echo $products->formatTXT(true);
echo "\n";
echo $earnings->formatTXT(true);
echo "\n";