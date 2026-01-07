# Dynamic formulas

Matrix cells can hold values or closures. When a cell contains a closure,
Matrix evaluates it like a spreadsheet formula and stores the result.

## Closure signature

Formulas receive `(row, column, matrix)` and should return the computed value.
Use the matrix argument to read other cells.

```php
<?php

use divengine\matrix;

$amount = fn ($r, $c, matrix $m) => $m->get($r, $c - 2) * $m->get($r, $c - 1);
```

## Computed columns

```php
<?php

$table->addColumn();
$table->set(0, 3, "Amount");
$table->fillVertical(3, 1, $table->getTotalRows() - 1, $amount);
```

## Workbook recalculation

All matrix instances are registered in a static workbook. When you update one
matrix, all matrices are re-evaluated, which enables cross-matrix formulas.
See `../examples/worksheet.php` for a multi-sheet example.
