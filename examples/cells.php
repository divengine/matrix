<?php

include __DIR__ . '/../src/matrix.php';

use divengine\matrix;

$m = new matrix([
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9]
]);

// echo $m->get(1, 1);
echo $m->{1.1};
echo "\n";

// echo $m->{0.2}; 
echo $m->{.2};
echo "\n";

// echo $m->{2.2};
echo $m->{-1.2};
echo "\n";

for ($i = 0; $i < 3; $i++)
    for ($j = 0; $j < 3; $j++)
        echo $m->{$i + ($j / 10)} . " ";
echo "\n";

for ($i = 0; $i < 3; $i++)
        for ($j = 0; $j < 3; $j++)
            echo $m->{($i + ($j / 10) + 1) * -1} . " ";
echo "\n";

for ($i = 0; $i < 3; $i++)
    for ($j = 0; $j < 3; $j++)
        echo $m->{"$i.$j"} . " ";

echo "\n";

$m->{0.0} = 10;
echo $m;