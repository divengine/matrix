<?php 

include __DIR__ . '/../src/matrix.php';

use divengine\matrix;

class MathTable extends matrix
{
    public function __construct($number, $size, $operation, $operator)
    {
        $data = [];
        for ($i = 1; $i <= $size; $i++)
        {
            $data[] = [$number,  $operator, $i, "=", $operation];
        }

        parent::__construct($data);
    }
}

class MultiplicationTable extends MathTable
{
    public function __construct($number = 1, $size = 10)
    {
        parent::__construct($number, $size, fn ($r, $c, $m)  => $m->get($r, 0) * $m->get($r, 2), "x");
    }
}

class AdditionTable extends MathTable
{
    public function __construct($number = 1, $size = 10)
    {
        parent::__construct($number, $size, fn ($r, $c, $m)  => $m->get($r, 0) + $m->get($r, 2), "+");
    }
}

for ($i = 1; $i <= 10; $i++)
{
    echo new MultiplicationTable($i);
    echo PHP_EOL;
}

echo PHP_EOL;

for ($i = 1; $i <= 10; $i++)
{
    echo new AdditionTable($i);
    echo PHP_EOL;
}