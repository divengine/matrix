[![Readme Card](https://github-readme-stats.vercel.app/api/pin/?username=divengine&repo=matrix&show_owner=true&rand=23)](https://github.com/anuraghazra/github-readme-stats)

# Div PHP Matrix

Div PHP Matrix is a PHP library for working with 2D data as matrices. It focuses
on dynamic cells that can hold values or formulas, enabling spreadsheet-style
logic for backend applications.

## Why use it

- Build calculations and business rules with closure-based cells.
- Recalculate automatically when inputs change, even across multiple matrices.
- Export data to common formats like CSV, JSON, XML, Markdown, and SQL.

## How it works

Each cell can be a value or a closure. Closures receive `(row, column, matrix)`
and can reference other cells. The library evaluates formulas and keeps the
computed values in sync.

## Ecosystem

Matrix integrates with other divengine/* projects (div, ways, nodes, ajaxmap,
and orm).
