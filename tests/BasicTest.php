<?php

namespace divengine\tests;

use PHPUnit\Framework\TestCase;
use divengine\matrix;

class BasicTest extends TestCase
{
	public function testCreateMatrix()
    {
        $data = [
            ["Product", "Price", "Count"],
            ["Apple", 10, 2],
            ["Banana", 35, 3],
            ["Orange", 6, 10],
            ["Orange", 7, 15],
        ];
        $table = new matrix($data);
        
        $this->assertEquals($data, $table->getMatrix());
    }

    public function testAddRow()
    {
        $table = new matrix([['Product', 'Price', 'Quantity', 'Amount']]);
        $product = (object) ["Apple", 1, 10, 10];
        $table->addRow(index: 'apple', row: $product);
        
        $expected = [
            ['Product', 'Price', 'Quantity', 'Amount'],
            'apple' => ['Apple', 1, 10, 10]
        ];
        
        $this->assertEquals($expected, $table->getMatrix());
    }

    public function testAddColumnAndFill()
    {
        $data = [
            ["Product", "Price", "Count"],
            ["Apple", 10, 2],
            ["Banana", 35, 3],
            ["Orange", 6, 10],
            ["Orange", 7, 15],
        ];
        $table = new matrix($data);
        
        $table->addColumn();
        $table->set(0, 3, "Amount");

        $table->fillVertical(3, 1, 4, 
            fn ($r, $c, matrix $m) 
                => $m->get($r, $c - 1) * $m->get($r, $c - 2));
        
        $expected = [
            ["Product", "Price", "Count", "Amount"],
            ["Apple", 10, 2, 20],
            ["Banana", 35, 3, 105],
            ["Orange", 6, 10, 60],
            ["Orange", 7, 15, 105],
        ];
        
        $this->assertEquals($expected, $table->getMatrix());
    }

    public function testGroupBy()
    {
        $data = [
            ["Product", "Price", "Count", "Amount"],
            ["Apple", 10, 2, 20],
            ["Banana", 35, 3, 105],
            ["Orange", 6, 10, 60],
            ["Orange", 7, 15, 105],
        ];

        $table = new matrix($data);
        
        $result = $table->groupBy([0], function($key, $group){
            $sum = 0;
            foreach($group as $row){
                $sum += $row[3];
            }
            return $sum;
        }, true);
        
        $expected = [
            'Apple' => 20,
            'Banana' => 105,
            'Orange' => 165,
        ];
        
        $this->assertEquals($expected, $result);
    }
}