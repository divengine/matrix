# Simple matrix

## Create, get, and set values

```php
<?php

require 'vendor/autoload.php';

use divengine\matrix;

// Simple list of numbers
$nums = new matrix([
    ["", 1, 2, 3],
    ["", 4, 5, 6],
]);

// Get item
echo $nums->get(1, 3); // 6

// Set item
$nums->set(1, 3, 10);

echo $nums->formatTXT();
```

## Ranges

```php
<?php

$range = $nums->range(0, 1, 1, 2); // [[1, 2], [4, 5]]
$rangeMatrix = new matrix($range);

echo $rangeMatrix->formatTXT();
```

## Edit the matrix

```php
<?php

$nums->addRow(["", 7, 8, 9]);
$nums->addColumn(0);
```

## Fill a column with a formula

```php
<?php

$nums->fillVertical(
    0,
    0,
    $nums->getTotalRows() - 1,
    fn ($r, $c, \divengine\matrix $m) => date('Y-m-d')
);
```
