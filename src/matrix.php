<?php

namespace divengine;

/**
 * [[]] Div PHP matrix
 *
 * A versatile utility for efficient manipulation of matrix data, providing methods 
 * for adding and removing rows, validating matrix integrity, and formatting data 
 * in various output formats. It also supports closures for dynamic values (spreadsheet-like).
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program as the file LICENSE.txt; if not, please see
 * https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package divengine/matrix
 * @author  Rafa Rodriguez @rafageist [https://rafageist.com]
 * @version 1.1.0
 *
 * @link    https://divengine.org/docs/div-php-matrix
 * @link    https://github.com/divengine/matrix
 */

use SimpleXMLElement;
use InvalidArgumentException;

class matrix
{
    public static $version = '1.1.0';
    private $matrix;
    private $matrix_original;
    private $evaluatedCells = [];
    private static $disableEvallAll = false;
    public const FORMAT_CSV = "CSV";
    public const FORMAT_XML = "XML";
    public const FORMAT_JSON = "JSON";
    public const FORMAT_JSON_OBJECTS = "JSON_OBJECTS";
    public const FORMAT_SERIALIZE = "SERIALIZE";
    public const FORMAT_HTML = "HTML";
    public const FORMAT_MARKDOWN = "MARKDOWN";
    public const FORMAT_YAML = "YAML";
    public const FORMAT_TXT = "TXT";
    public const FORMAT_SQL = "SQL";
    private $defaultFormat = self::FORMAT_TXT;
    public static $workbook = [];

    /**
     * Constructor
     * 
     * @param array $matrix
     * 
     * @throws InvalidArgumentException
     */
    public function __construct(array $matrix)
    {
        // convert object rows to array
        foreach ($matrix as $rowIndex => $row) {
            if (is_object($row)) {
                $matrix[$rowIndex] = (array) $row;
            }
        }

        $this->validateMatrix($matrix);
        $this->matrix = $matrix;
        $this->matrix_original = $matrix;

        self::$workbook[] = $this;
        self::evaluateAll();
    }

    /**
     * Validates the matrix
     * 
     * @param array $matrix
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function validateMatrix(array $matrix): void
    {
        if (empty($matrix) || !is_array($matrix[0]) || empty($matrix[0])) {
            throw new InvalidArgumentException('Matrix must have at least one row with string headers');
        }

        $numColumns = count($matrix[0]);
        foreach ($matrix as $row) {
            if (!is_array($row) || count($row) !== $numColumns) {
                throw new InvalidArgumentException('Not all rows have the same number of elements');
            }
        }
    }

    /**
     * Add a row
     * 
     * @param array $row
     * 
     * @return int
     */
    public function addRow(array $row, bool $onTop = false): void
    {
        if (!$this->validateRow($row)) {
            throw new InvalidArgumentException('Row does not have the same number of elements as the first row');
        }

        if ($onTop) {
            array_unshift($this->matrix, $row);
            array_unshift($this->matrix_original, $row);
        } else {
            $this->matrix[] = $row;
            $this->matrix_original[] = $row;
        }

        self::evaluateAll();
    }

    /**
     * Add a column
     * 
     * @param mixed $value
     * 
     * @return void
     */
    public function addColumn($value = null): void
    {
        foreach ($this->matrix as $rowIndex => $row) {
            $this->matrix[$rowIndex][] = $value;
            $this->matrix_original[$rowIndex][] = $value;
        }

        self::evaluateAll();
    }

    /**
     * Remove a row
     * 
     * @param int $index
     * 
     * @return void
     */
    public function removeRow(int $index): void
    {
        if ($index >= 0 && $index < count($this->matrix)) {

            // remove row from matrix and original matrix but slice
            // the array to avoid gaps in the indexes
            $this->matrix = array_merge(
                array_slice($this->matrix, 0, $index),
                array_slice($this->matrix, $index + 1)
            );

            $this->matrix_original = array_merge(
                array_slice($this->matrix_original, 0, $index),
                array_slice($this->matrix_original, $index + 1)
            );

            self::evaluateAll();
        }
    }

    /**
     * Get data
     * 
     * @return array
     */
    public function getMatrix(): array
    {
        return $this->matrix;
    }

    /**
     * Validate a row
     * 
     * @param array $row
     * 
     * @throws InvalidArgumentException
     * 
     * @return bool
     */
    private function validateRow($row): bool
    {
        return !(!is_array($row) || count($row) !== count($this->matrix[0]));
    }

    /**
     * Format
     * 
     * @param string $outputFormat
     * 
     * @throws InvalidArgumentException
     * 
     * @return string
     */
    public function format(string $outputFormat, bool $firstRowAsHeaders = false): string
    {
        switch (strtoupper($outputFormat)) {
            case self::FORMAT_CSV:
                return $this->formatCSV();
            case self::FORMAT_XML:
                return $this->formatXML(firstRowAsHeaders: $firstRowAsHeaders);
            case self::FORMAT_JSON:
                return $this->formatJSON($firstRowAsHeaders);
            case self::FORMAT_JSON_OBJECTS:
                return $this->formatJSON($firstRowAsHeaders);
            case self::FORMAT_SERIALIZE:
                return $this->formatSerialize();
            case self::FORMAT_HTML:
                return $this->formatHTML($firstRowAsHeaders);
            case self::FORMAT_MARKDOWN:
                return $this->formatMarkdown($firstRowAsHeaders);
            case self::FORMAT_YAML:
                return $this->formatYAML($firstRowAsHeaders);
            case self::FORMAT_TXT:
                return $this->formatTXT($firstRowAsHeaders);
            case self::FORMAT_SQL:
                return $this->formatSQL('table', $firstRowAsHeaders);
            default:
                throw new InvalidArgumentException('Unsupported format');
        }
    }

    /**
     * Format CSV
     * @see https://www.ietf.org/rfc/rfc4180.txt
     * 
     * @return string
     */
    public function formatCSV(): string
    {
        $result = '';
        foreach ($this->matrix as $row) {
            $result .= '"' . implode('";"', $row) . '"' . PHP_EOL;
        }
        return $result;
    }

    /**
     * Format XML
     * @see https://www.php.net/manual/en/simplexml.examples-basic.php 
     *     
     * @param string $rootTag
     * @param bool $firstRowAsHeaders
     * 
     * @return string     
     */
    public function formatXML(string $rootTag = 'root', bool $firstRowAsHeaders = false)
    {
        $xml = new SimpleXMLElement("<{$rootTag}/>");

        if ($firstRowAsHeaders) {
            $fields = $xml->addChild('fields');
            foreach ($this->matrix[0] as $field) {
                $fields->addChild('field', $field);
            }

            $data = $xml->addChild('data');
            foreach (array_slice($this->matrix, 1) as $row) {
                $item = $data->addChild('row');
                foreach ($this->matrix[0] as $index => $field) {
                    $item->addChild($field, $row[$index]);
                }
            }
        } else {
            foreach ($this->matrix as $row) {
                $item = $xml->addChild('row');
                foreach ($row as $index => $field) {
                    $item->addChild('field', $field);
                }
            }
        }

        return $xml->asXML();
    }

    /**
     * Format JSON
     * @see https://www.php.net/manual/en/function.json-encode.php
     * 
     * @param bool $asObjects
     * @return string
     */
    public function formatJSON(bool $asObjects = false)
    {
        if ($asObjects) {
            $result = [];
            foreach (array_slice($this->matrix, 1) as $row) {

                // convert header names to lower snake case
                $header = array_map(function ($field) {
                    return str_replace(' ', '_', strtolower($field));
                }, $this->matrix[0]);

                // combine header names with row values
                $result[] = array_combine($header, $row);
            }
            return json_encode($result);
        }
        return json_encode($this->matrix);
    }

    /**
     * Format Serialize
     * @see https://www.php.net/manual/en/function.serialize.php
     * 
     * @return string
     */
    public function formatSerialize()
    {
        return serialize($this->matrix);
    }

    /**
     * Format HTML
     * @see https://www.w3schools.com/html/html_tables.asp
     * 
     * @param bool $firstRowAsHeaders
     * 
     * @return string
     */
    public function formatHTML(bool $firstRowAsHeaders = false): string
    {
        $html = '<table>';

        if ($firstRowAsHeaders) {
            $html .= '<tr>';
            foreach ($this->matrix[0] as $field) {
                $html .= '<th>' . $field . '</th>';
            }
            $html .= '</tr>';

            foreach (array_slice($this->matrix, 1) as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . $value . '</td>';
                }
                $html .= '</tr>';
            }
        } else {
            foreach ($this->matrix as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . $value . '</td>';
                }
                $html .= '</tr>';
            }
        }

        $html .= '</table>';
        return $html;
    }

    /**
     * Format Markdown
     * @see https://www.markdownguide.org/extended-syntax/#tables
     * 
     * @param bool $firstRowAsHeaders
     * 
     * @return string
     */
    public function formatMarkdown(bool $firstRowAsHeaders = false): string
    {
        $markdown = '';
        if ($firstRowAsHeaders) {
            $markdown = '| ' . implode(' | ', $this->matrix[0]) . " |\n";
            $markdown .= '| ' . str_repeat('--- | ', count($this->matrix[0])) . "\n";
            foreach (array_slice($this->matrix, 1) as $row) {
                $markdown .= '| ' . implode(' | ', $row) . " |\n";
            }
        } else {
            foreach ($this->matrix as $row) {
                $markdown .= '| ' . implode(' | ', $row) . " |\n";
            }
        }

        return $markdown;
    }

    /**
     * Format YAML
     * (as Sequence of Mappings)
     * @see https://yaml.org/spec/1.2.2/
     * 
     * @param bool $firstRowAsHeaders
     * 
     * @return string
     */
    public function formatYAML(bool $firstRowAsHeaders = false): string
    {
        $array = $this->matrix;
        $yaml = '';

        if ($firstRowAsHeaders) {
            // Process the first row (column names)
            $headers = array_shift($array);
            $yaml .= 'fields:' . PHP_EOL;
            foreach ($headers as $header) {
                $formattedHeader = str_replace(' ', '_', strtolower($header));
                $yaml .= "  - $formattedHeader" . PHP_EOL;
            }
        }

        // Process the remaining rows
        $yaml .= 'data:' . PHP_EOL;
        foreach ($array as $item) {
            $yaml .= '  - ';

            $firstKey = true;
            foreach ($item as $key => $value) {
                // Convert the key to lowercase and replace spaces with underscores
                $formattedKey = str_replace(' ', '_', strtolower($headers[$key]));

                // Add the first field on the same line with the dash, the rest aligned
                if (!$firstKey) {
                    $yaml .= "    "; // Add 4 spaces
                }

                // Key and value for each pair
                $yaml .= "$formattedKey: $value" . PHP_EOL;

                $firstKey = false;
            }
        }

        return $yaml;
    }

    /**
     * Format TXT
     * 
     * @param bool $firstRowAsHeaders
     * 
     * @return string
     * @throws InvalidArgumentException
     */
    public function formatTXT(bool $firstRowAsHeaders = false): string
    {
        $txt = '';
        if ($firstRowAsHeaders) {
            $txt = implode("\t", $this->matrix[0]) . PHP_EOL;
            foreach (array_slice($this->matrix, 1) as $row) {
                $txt .= implode("\t", $row) . PHP_EOL;
            }
        } else {
            foreach ($this->matrix as $row) {
                $txt .= implode("\t", $row) . PHP_EOL;
            }
        }

        return $txt;
    }

    /**
     * Format SQL
     * 
     * @param string $tableName
     * @param bool $firstRowAsHeaders
     * 
     * @return string
     */
    public function formatSQL(string $tableName, bool $firstRowAsHeaders = false): string
    {
        $sql = '';
        if ($firstRowAsHeaders) {

            // headers as snake case
            $headers = array_map(function ($field) {
                return str_replace(' ', '_', strtolower($field));
            }, $this->matrix[0]);

            $sql = 'INSERT INTO ' . $tableName . ' (' . implode(', ', $headers) . ') VALUES' . PHP_EOL;
            foreach (array_slice($this->matrix, 1) as $row) {

                // escape values
                foreach ($row as $index => $value) {
                    if (is_string($value)) {
                        $row[$index] = "'$value'";
                    } elseif (is_null($value)) {
                        $row[$index] = 'NULL';
                    } elseif (is_bool($value)) {
                        $row[$index] = $value ? 'TRUE' : 'FALSE';
                    } elseif (is_array($value)) {
                        $row[$index] = json_encode($value);
                    } elseif (is_object($value)) {
                        $row[$index] = json_encode($value);
                    } else {
                        $row[$index] = "$value";
                    }
                }

                $sql .= '(' . implode(', ', $row) . ')' . PHP_EOL;
            }
        } else {
            foreach ($this->matrix as $row) {
                $sql .= '(' . implode(', ', $row) . ')' . PHP_EOL;
            }
        }

        return $sql . ';' . PHP_EOL;
    }

    /**
     * Fill a horizontal range
     * 
     * @param int $row
     * @param int $from
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function fillHorizontal(int $row, int $from, int $to, mixed $value): void
    {
        if ($from < 0 || $to > count($this->matrix[0])) {
            throw new InvalidArgumentException('Invalid range');
        }

        self::$disableEvallAll = true;
        for ($i = $from; $i <= $to; $i++) {
            $this->set($row, $i, $value);
        }
        self::$disableEvallAll = false;

        self::evaluateAll();
    }

    /**
     * Fill a vertical range
     * 
     * @param int $column
     * @param int $from
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function fillVertical(int $column, int $from, int $to, $value): void
    {
        if ($from < 0 || $to > count($this->matrix)) {
            throw new InvalidArgumentException('Invalid range');
        }

        self::$disableEvallAll = true;
        for ($i = $from; $i <= $to; $i++) {
            $this->set($i, $column, $value);
        }
        self::$disableEvallAll = false;

        self::evaluateAll();
    }

    /**
     * Evaluate a cell
     * 
     * @param int $row
     * @param int $column
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function eval(int $row, int $column): void
    {
        $this->validateCoordinates($row, $column);

        if ($this->matrix_original[$row][$column] instanceof \Closure) {
            if (!isset($this->evaluatedCells[$row][$column])) {
                $this->matrix[$row][$column] = $this->matrix_original[$row][$column]($row, $column, $this);
                $this->evaluatedCells[$row][$column] = true;
            }
        }
    }

    /**
     * Validate coordinates
     * 
     * @param int $row
     * @param int $column
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function validateCoordinates(int $row, int $column): void
    {
        // non negative indexes
        if ($row < 0 || $column < 0) {
            throw new InvalidArgumentException('Invalid cell');
        }

        // no overflow indexes
        if ($row >= count($this->matrix) || $column >= count($this->matrix[0])) {
            throw new InvalidArgumentException('Invalid cell');
        }
    }
    /**
     * Set a value in a cell
     * 
     * @param int $row
     * @param int $column
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function set(int $row, int $column, $value): void
    {
        $this->validateCoordinates($row, $column);

        $this->matrix_original[$row][$column] = $value;
        $this->matrix[$row][$column] = $value;

        self::evaluateAll();
    }

    /**
     * Get a value from a cell
     * 
     * @param int $row
     * @param int $column
     * 
     * @throws InvalidArgumentException
     * 
     * @return mixed
     */
    public function get(int $row, int $column)
    {
        $this->validateCoordinates($row, $column);

        $this->eval($row, $column);

        return $this->matrix[$row][$column];
    }

    /**
     * Process all closures
     * 
     * @return void
     */
    public function evaluate(): void
    {
        if (self::$disableEvallAll) {
            return;
        }

        // reset evaluated cells
        $this->evaluatedCells = [];

        foreach ($this->matrix_original as $rowIndex => $row) {
            foreach ($row as $colIndex => $col) {
                if ($col instanceof \Closure) {
                    try {
                        $this->eval($rowIndex, $colIndex);
                    } catch (\Exception $e) {
                        $this->matrix[$rowIndex][$colIndex] = $e->getMessage();
                    }
                }
            }
        }
    }

    /**
     * Process all matrixes
     * 
     * @return void
     */
    public static function evaluateAll(): void
    {
        if (self::$disableEvallAll) {
            return;
        }

        foreach (self::$workbook as $worksheet) {
            $worksheet->evaluate();
        }
    }

    /**
     * Get a horizontal range
     * 
     * @param int $row
     * @param int $from
     * @param int $to
     * 
     * @throws InvalidArgumentException
     * 
     * @return array
     */
    public function horizontal(int $row, int $from, int $to): array
    {
        if ($from < 0 || $to > count($this->matrix[0])) {
            throw new InvalidArgumentException('Invalid range');
        }

        return array_slice($this->matrix[$row], $from, $to - $from + 1);
    }

    /**
     * Get a vertical range
     * 
     * @param int $column
     * @param int $from
     * @param int $to
     * 
     * @throws InvalidArgumentException
     * 
     * @return array
     */
    public function vertical(int $column, int $from, int $to): array
    {
        if ($from < 0 || $to > count($this->matrix)) {
            throw new InvalidArgumentException('Invalid range');
        }

        $result = [];
        for ($i = $from; $i <= $to; $i++) {
            $result[] = $this->matrix[$i][$column];
        }
        return $result;
    }

    /**
     * Get a range
     * 
     * @param int $rowFrom
     * @param int $columnFrom
     * @param int $rowTo
     * @param int $columnTo
     * 
     * @throws InvalidArgumentException
     * 
     * @return array
     */
    public function range(int $rowFrom, int $columnFrom, int $rowTo, int $columnTo): array
    {
        (
            $rowFrom >= 0
        and $rowTo <= count($this->matrix)
        and $columnFrom >= 0
        and $columnTo <= count($this->matrix[0])
        and $rowFrom <= $rowTo
        and $columnFrom <= $columnTo
        )
        or throw new InvalidArgumentException('Invalid range');

        $result = [];
        for ($i = $rowFrom; $i <= $rowTo; $i++) {
            $result[] = array_slice($this->matrix[$i], $columnFrom, $columnTo - $columnFrom + 1);
        }
        return $result;
    }

    /**
     * Group by a column
     * 
     * @param int $column
     * @param bool $firstRowAsHeaders
     * 
     * @return array
     */
    public function groupBy(array $columns, \Closure $aggregate = null, bool $firstRowAsHeaders = false): array
    {
        $data = $this->matrix;

        $result = [];
        if ($firstRowAsHeaders) {
            $headers = array_shift($data);
        }

        foreach ($data as $row) {
            $key = [];
            foreach ($columns as $column) {
                $key[] = $row[$column];
            }

            $key = implode('-', $key);
            $result[$key][] = $row;
        }

        if ($aggregate) {
            foreach ($result as $key => $group) {
                $aggregation = $aggregate($key, $group);
                if ($aggregation !== null) {
                    $result[$key] = $aggregation;
                }
            }
        }

        return $result;
    }

    public function getTotalRows()
    {
        return count($this->matrix);
    }

    public function getTotalColumns()
    {
        return count($this->matrix[0]);
    }   

    /**
     * Get a row
     * 
     * @param int $row
     * 
     * @return array
     */
    public function getRow(int $row): array
    {
        return $this->matrix[$row];
    }

    /**
     * Get a column
     * 
     * @param int $column
     * 
     * @return array
     */
    public function getColumn(int $column): array
    {
        $result = [];
        foreach ($this->matrix as $row) {
            $result[] = $row[$column];
        }
        return $result;
    }

    /**
     * Get a row with formulas
     * 
     * @param int $row
     * 
     * @return array
     */
    public function getRowWithFormulas(int $row): array
    {
        return $this->matrix_original[$row];
    }

    /**
     * Get a column with formulas
     * 
     * @param int $column
     * 
     * @return array
     */
    public function getColumnWithFormulas(int $column): array
    {
        $result = [];
        foreach ($this->matrix_original as $row) {
            $result[] = $row[$column];
        }
        return $result;
    }

    /**
     * Validate a format
     * 
     * @param string $format
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    private function validateFormat(string $format): void
    {
        if (!in_array($format, [
            self::FORMAT_CSV,
            self::FORMAT_XML,
            self::FORMAT_JSON,
            self::FORMAT_JSON_OBJECTS,
            self::FORMAT_SERIALIZE,
            self::FORMAT_HTML,
            self::FORMAT_MARKDOWN,
            self::FORMAT_YAML,
            self::FORMAT_TXT,
            self::FORMAT_SQL,
        ])) {
            throw new InvalidArgumentException('Invalid format');
        }
    }

    /**
     * Get the default format
     * 
     * @return string
     */
    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }

    /**
     * Set the default format
     * 
     * @param string $format
     * 
     * @return void
     */
    public function setDefaultFormat(string $format)
    {
        $this->validateFormat($format);
        $this->defaultFormat = $format;
    }

    /**
     * Magic method to convert the matrix to string
     * 
     * @return string
     */
    public function __toString()
    {
        return $this->format($this->defaultFormat);
    }
}
