# Matrix features

## Core capabilities

- Create matrices from arrays, objects, dimensions, CSV, JSON, TXT, or serialized data.
- Validate shape and keep all rows with the same number of columns.
- Access and edit cells (`get`, `set`), rows, and columns.
- Add, insert, remove, and rename rows or columns.
- Work with ranges (`horizontal`, `vertical`, `range`) and fill ranges with values or formulas.
- Use dynamic cells (closures) with automatic recalculation across all matrices.
- Group and aggregate rows with `groupBy`.
- Export to CSV, XML, JSON, JSON objects, serialize, HTML, Markdown, YAML, TXT, and SQL.

## Handy shortcuts

- `rows` and `columns` magic properties.
- `$m->{1.2}` or `$m->{"1.2"}` to access row 1, column 2.
- `.2` means row 0, column 2 when using numeric property access.

## Example: grouping and aggregation

```php
<?php

use divengine\matrix;

$table = new matrix([
    ["Product", "Price", "Count", "Amount"],
    ["Apple", 10, 2, 20],
    ["Banana", 35, 3, 105],
    ["Orange", 6, 10, 60],
    ["Orange", 7, 15, 105],
]);

$result = $table->groupBy([0], function ($key, $group) {
    $sum = 0;
    foreach ($group as $row) {
        $sum += $row[3];
    }
    return $sum;
}, true);
```

## Related docs

- [Simple matrix](Features/Simple%20matrix.md)
- [Dynamic formulas](Features/Dynamic%20formulas.md)
- [Formatting and export](Features/Formatting%20and%20export.md)
