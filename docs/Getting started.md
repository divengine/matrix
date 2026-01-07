# Getting started

## Installation

```shell
composer require divengine/matrix
```

## Quick start

```php
<?php

require 'vendor/autoload.php';

use divengine\matrix;

$table = new matrix([
    ["Product", "Price", "Count"],
    ["Apple", 10, 2],
    ["Banana", 35, 3],
]);

echo $table->get(1, 0); // Apple

$table->set(1, 2, 5);
echo $table->formatTXT(true);
```

## Formula cells (spreadsheet style)

```php
<?php

// Assuming $table is created as shown above.
$amount = fn ($r, $c, \divengine\matrix $m) => $m->get($r, $c - 2) * $m->get($r, $c - 1);

$table->addColumn();
$table->set(0, 3, "Amount");
$table->fillVertical(3, 1, $table->getTotalRows() - 1, $amount);
```

Formula closures receive `(row, column, matrix)` and are re-evaluated
automatically when data changes.

## Import and export

```php
<?php

$fromCsv = matrix::fromCSVFile('data.csv');
echo $fromCsv->format(matrix::FORMAT_JSON);

echo $table->formatJSON(true); // header row as object keys
```

## Next steps

- Browse [Features](Features/) for focused topics.
- See `../examples` and `../tests` for runnable patterns.
