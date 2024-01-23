<?php 

include __DIR__ . '/../src/matrix.php';

use divengine\matrix;

$F_AMOUNT = fn($r, $c, matrix $m) => $m->{$r + .1} * $m->{$r +.2};
$F_TOTAL = fn($r, $c, matrix $m) => array_sum($m->vertical($c, 1, $m->rows - 1));
$F_AVG = fn($r, $c, matrix $m) => $F_TOTAL($r, $c, $m) / ($m->rows - 1);

class ProductsTable extends matrix
{
    public function __construct(array $products)
    {
        global $F_AMOUNT, $F_AVG, $F_TOTAL;
        
        $data = [["Name", "Price", "Qty", "Total"]];
        foreach ($products as $product)
        {
            $data[] = [$product->name, $product->price, $product->quantity, $F_AMOUNT];
        }

        $data[] = ["Total", $F_AVG, $F_TOTAL, $F_TOTAL];

        parent::__construct($data);
    }
}

echo new ProductsTable([
    (object) ["name" => "Apple", "price" => 1.5, "quantity" => 10],
    (object) ["name" => "Banana", "price" => 2.5, "quantity" => 5],
    (object) ["name" => "Orange", "price" => 3.5, "quantity" => 3],
    (object) ["name" => "Kiwi", "price" => 4.5, "quantity" => 1],
]);
