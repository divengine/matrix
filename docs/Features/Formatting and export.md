# Formatting and export

Matrix can export data using `format()` or the dedicated `format*` methods.
Most formats optionally treat the first row as headers.

## Supported formats

- CSV, XML, JSON, JSON objects, serialize, HTML, Markdown, YAML, TXT, SQL

## Examples

```php
<?php

use divengine\matrix;

echo $table->format(matrix::FORMAT_CSV);
echo $table->format(matrix::FORMAT_MARKDOWN, true);

echo $table->formatJSON(true); // header row becomes object keys (lower snake case)
echo $table->formatXML('root', true);
echo $table->formatSQL('products', true);
```

If you need a custom SQL table name, use `formatSQL()` instead of `format()`,
since `format()` uses a default table name.
