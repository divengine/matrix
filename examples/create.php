<?php

include __DIR__ . '/../src/matrix.php';

use divengine\matrix;

// bidimensional array of random numbers
$array = matrix::createArrayFromDims(5, 5, 0);

// Create a new object matrix
$m1 = new matrix($array);
echo $m1->formatTXT(false);
echo PHP_EOL;

// Create a new matrix using the static method create
$m2 = matrix::create($array);
echo $m2->formatTXT(false);
echo PHP_EOL;

// Create an matrix from dimensions
$m3 = matrix::dimension(5, 5, 0);
echo $m3->formatTXT(false);
echo PHP_EOL;

// Create an matrix from a string
$m4 = matrix::fromJSONFile(__DIR__.'/data/matrix.json');
echo $m4->formatTXT(false);
echo PHP_EOL;
