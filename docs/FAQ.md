# FAQ

## What indexing does Matrix use?

Matrix uses zero-based indexes for rows and columns by default. You can also use
string keys for rows and columns when you build the matrix or add rows.

## How do formula cells work?

Any cell can be a `Closure`. The closure is called with `(row, column, matrix)`
and its return value becomes the cell value. Formulas are re-evaluated
automatically whenever data changes.

## How do I access a cell quickly?

Use `get($row, $column)`, or property access:
`$m->{1.2}` / `$m->{"1.2"}` for row 1, column 2. A numeric property like `.2`
means row 0, column 2. Negative row indexes in numeric properties count from
the end (see `examples/cells.php`).

## Can I use objects as rows?

Yes. The constructor and `addRow()` accept objects; they are cast to arrays.

## Can I treat the first row as headers?

Yes. Pass `true` to formats that support headers, like `formatTXT(true)` or
`formatJSON(true)` (JSON objects). `groupBy(..., true)` ignores the header row
when grouping.

## What output formats are supported?

CSV, XML, JSON, JSON objects, serialize, HTML, Markdown, YAML, TXT, and SQL.

## How do I group and aggregate data?

Use `groupBy()` with an array of column indexes and an optional aggregate
closure. See `tests/BasicTest.php` and `examples/grouping.php` for patterns.

## Why do I get "Invalid range" or "Invalid cell"?

Matrix validates shape and bounds. Make sure all rows have the same length and
the indexes you use exist. `existsRow()`, `existsColumn()`, and `existsCell()`
can help.
