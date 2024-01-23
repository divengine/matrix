<?php

include __DIR__ . '/../src/matrix.php';

use divengine\matrix;

$m = new matrix([
    [1, 2, 3],
    [4, 5, 6],
    [7, 8, 9]
]);

$m->insertAfterRow(1, [10, 11, 12]);

echo $m;
echo "\n";

$m->insertBeforeRow(1, [13, 14, 15]);

echo $m;
echo "\n";

$m->insertAfterColumn(1, 0);
echo $m;
echo "\n";

$m->insertBeforeColumn(1, 0);
echo $m;
echo "\n";

$m->removeColumn(1);
echo $m;
echo "\n";

$m->removeRow(2);
echo $m;
echo "\n";

$m->removeColumn(2);
echo $m;
echo "\n";

$m->addColumn(5);
echo $m;
echo "\n";

$m->removeColumnRange(1, 3);
echo $m;
echo "\n";

$m->addColumn(6);
$m->addRow([2, 2]);
echo $m;
echo "\n";

$m->removeRowRange(1, 3);
echo $m;
echo "\n";

$m->addColumn(7);
$m->addRow([3, 3, 3]);
echo $m;
echo "\n";

$m->fillRange(1, 1, 2, 2, 0);
echo $m;
echo "\n";

$m->insertAfterColumn(1, [1, 2, 3]);
echo $m;
echo "\n";

$m->insertBeforeColumn(1, [4, 5, 6]);
echo $m;
echo "\n";
