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
 * @version 1.3.0
 *
 * @link    https://divengine.org/
 * @link    https://github.com/divengine/matrix
 */

use Closure;
use SimpleXMLElement;
use InvalidArgumentException;

class matrix
{
    public static string $version = '1.3.0';

    /** @var array<array<mixed>> $matrix */
    private array $matrix = [];

    /** @var array<array<mixed>> $matrix_original */
    private array $matrix_original = [];

    /** @var array<array<bool>> $evaluatedCells */
    private array $evaluatedCells = [];

    private static bool $disableEvallAll = false;
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
    private string $defaultFormat = self::FORMAT_TXT;

    /** @var array<matrix> $workbook */
    public static array $workbook = [];

    /**
     * Constructor
     * 
     * @param array<array<mixed>|object> $matrixData
     * 
     * @throws InvalidArgumentException
     */
    public function __construct(array $matrixData)
    {
        // convert object rows to array
        /** @var array<array<mixed>> $data */
        $data = [];
        foreach ($matrixData as $rowIndex => $row) {
            if (is_object($row)) {
                $data[$rowIndex] = (array) $row;
            } else {
                $data[$rowIndex] = $row;
            }
        }

        $this->validateMatrix($data);
        $this->matrix = $data;
        $this->matrix_original = $data;

        self::$workbook[] = $this;
        self::evaluateAll();
    }

    /**
     * Create a matrix
     * 
     * @param array<array<mixed>|object> $matrix
     * 
     * @return matrix
     */
    public static function create(array $matrix)
    {
        return new self($matrix);
    }

    /**
     * Create a array from dimensions
     * 
     * @param int $rows
     * @param int $cols
     * @param mixed $value
     * 
     * @return array<array<mixed>>
     */
    public static function createArrayFromDims(int $rows, int $cols, $value = null)
    {
        $matrix = [];
        for ($i = 0; $i < $rows; $i++) {
            $matrix[] = array_fill(0, $cols, $value);
        }
        return $matrix;
    }

    /**
     * Create a matrix from dimensions
     * 
     * @param int $rows
     * @param int $cols
     * @param mixed $value
     * 
     * @return matrix
     */
    public static function dimension(int $rows, int $cols, $value = null)
    {
        return new self(self::createArrayFromDims($rows, $cols, $value));
    }

    /**
     * Create a matrix from a CSV file
     * 
     * @param string $filename
     * @param string $delimiter
     * @param string $enclosure
     * @param string $escape
     * 
     * @return matrix
     */
    public static function fromCSVFile(string $filename, string $delimiter = ',', string $enclosure = '"', string $escape = '\\'): matrix
    {
        $matrix = [];
        if (($handle = fopen($filename, "r")) !== false) {
            while (($data = fgetcsv($handle, 0, $delimiter, $enclosure, $escape)) !== false) {
                $matrix[] = $data;
            }
            fclose($handle);
        }
        return new self($matrix);
    }

    /**
     * Create a matrix from a JSON file
     * 
     * @param string $filename
     * 
     * @throws InvalidArgumentException
     * 
     * @return matrix
     */
    public static function fromJSONFile(string $filename): matrix
    {
        $json = file_get_contents($filename);
        if ($json) {
            $matrix = json_decode($json, true);

            /** @var array<array<mixed>|object> $matrix */
            return new self($matrix);
        }

        throw new InvalidArgumentException('Invalid JSON file');
    }

    /**
     * Create a matrix from a TXT file
     * 
     * @param string $filename
     * @param string $delimiter
     * 
     * @return matrix
     */
    public static function fromTXTFile(string $filename, string $delimiter = "\t"): matrix
    {
        $matrix = [];
        if (($handle = fopen($filename, "r")) !== false) {
            while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                $matrix[] = $data;
            }
            fclose($handle);
        }
        return new self($matrix);
    }

    /**
     * Create a matrix from a serialized file
     * 
     * @param string $filename
     * 
     * @throws InvalidArgumentException
     * 
     * @return matrix
     */
    public static function fromSerializedFile(string $filename): matrix
    {
        $serialized = file_get_contents($filename);
        if ($serialized) {
            $matrix = unserialize($serialized);

            /** @var array<array<mixed>|object> $matrix */
            return new self($matrix);
        }

        throw new InvalidArgumentException('Invalid serialized file');
    }

    /**
     * Create a matrix from a serialized string
     * 
     * @param string $string
     * 
     * @return matrix
     */

    public static function fromSerializedString(string $string): matrix
    {
        $matrix = unserialize($string);

        /** @var array<array<mixed>|object> $matrix */
        return new self($matrix);
    }

    /**
     * Validates the matrix
     * 
     * @param array<array<mixed>> $matrix
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function validateMatrix(array $matrix): void
    {
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
     * @param array<mixed>|object $row
     * @param bool $onTop
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function addRow(array|object $row, bool $onTop = false, mixed $index = null): void
    {
        if (is_object($row)) {
            $row = (array) $row;
        }

        if (!$this->validateRow($row)) {
            throw new InvalidArgumentException('Row does not have the same number of elements as the first row');
        }

        if ($onTop) {
            array_unshift($this->matrix, $row);
            array_unshift($this->matrix_original, $row);
        } else {
            if ($index !== null) {
                $this->matrix[$index] = $row;
                $this->matrix_original[$index] = $row;
            } else {
                $this->matrix[] = $row;
                $this->matrix_original[] = $row;
            }
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
    public function removeRow(mixed $index, bool $reorder = true): void
    {
        if ($this->existsRow($index)) {
            unset($this->matrix[$index]);
            unset($this->matrix_original[$index]);

            if ($reorder)
            {
                $newMatrix = [];
                $newMatrixOriginal = [];
                foreach ($this->matrix as $rowIndex => $row) {
                    $newIndex = $rowIndex;
                    if (is_int($index) && $rowIndex > $index) {
                        $newIndex--;
                    }
                    $newMatrix[$newIndex] = $row;
                    $newMatrixOriginal[$newIndex] = $this->matrix_original[$rowIndex];
                }

                $this->matrix = $newMatrix;
                $this->matrix_original = $newMatrixOriginal;
            }

            self::evaluateAll();
        }
    }

    /**
     * Get data
     * 
     * @return array<array<mixed>>
     */
    public function getMatrix(): array
    {
        return $this->matrix;
    }

    /**
     * Validate a row
     * 
     * @param array<mixed> $row
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
    public function formatXML(string $rootTag = 'root', bool $firstRowAsHeaders = false): string
    {
        $xml = new SimpleXMLElement("<{$rootTag}/>");

        if ($firstRowAsHeaders) {
            $fields = $xml->addChild('fields');
            foreach ($this->matrix[0] as $field) {
                $fields->addChild('field', $field . "");
            }

            $data = $xml->addChild('data');
            foreach (array_slice($this->matrix, 1) as $row) {
                $item = $data->addChild('row');
                foreach ($this->matrix[0] as $index => $field) {
                    $item->addChild($field . "", $row[$index] . "");
                }
            }
        } else {
            foreach ($this->matrix as $row) {
                $item = $xml->addChild('row');
                foreach ($row as $index => $field) {
                    $item->addChild('field', $field . "");
                }
            }
        }

        return $xml->asXML() . "";
    }

    /**
     * Format JSON
     * @see https://www.php.net/manual/en/function.json-encode.php
     * 
     * @param bool $asObjects
     * @return string
     */
    public function formatJSON(bool $asObjects = false): string
    {
        if ($asObjects) {
            $result = [];
            // convert header names to lower snake case
            $header = array_map(function ($field) {
                return str_replace(' ', '_', strtolower($field . ""));
            }, $this->matrix[$this->getFirstRowIndex()]);

            $first = true;
            foreach ($this->matrix as $row) {
                
                // ignore first row
                if ($first)
                {
                    $first = false;
                    continue;
                }

                // combine header names with row values
                $result[] = array_combine($header, $row);
            }
            return json_encode($result) . "";
        }
        return json_encode($this->matrix) . "";
    }

    /**
     * Format Serialize
     * @see https://www.php.net/manual/en/function.serialize.php
     * 
     * @return string
     */
    public function formatSerialize(): string
    {
        return serialize($this->matrix) . "";
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

        $headers = [];

        // by default headers are a list of consecutive numbers
        for ($i = 0; $i < count($array[0]); $i++) {
            $headers[] = $i;
        }

        if ($firstRowAsHeaders) {
            // Process the first row (column names)
            $headers = array_shift($array);
            $yaml .= 'fields:' . PHP_EOL;
            if (is_array($headers)) {
                foreach ($headers as $header) {
                    $formattedHeader = str_replace(' ', '_', strtolower($header . ""));
                    $yaml .= "  - $formattedHeader" . PHP_EOL;
                }
            }
        }

        // Process the remaining rows
        $yaml .= 'data:' . PHP_EOL;
        foreach ($array as $item) {
            $yaml .= '  - ';

            $firstKey = true;
            $i = 0;
            foreach ($item as $key => $value) {
                $i++;
                // Convert the key to lowercase and replace spaces with underscores
                $unformattedKey = $headers[$key] ?? "$i";
                $formattedKey = str_replace(' ', '_', strtolower($unformattedKey . ""));

                // Add the first field on the same line with the dash, the rest aligned
                if (!$firstKey) {
                    $yaml .= "    "; // Add 4 spaces
                }

                // Key and value for each pair
                $yaml .= $formattedKey . ": " . $value . PHP_EOL;

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
                return str_replace(' ', '_', strtolower($field . ""));
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
                        $row[$index] = $value . "";
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
    public function fillHorizontal(mixed $row, mixed $from, mixed $to, mixed $value): void
    {
        if (!$this->existsCell($row , $from) || !$this->existsCell($row , $to)) {
            throw new InvalidArgumentException('Invalid range');
        }

        self::$disableEvallAll = true;

        foreach ($this->matrix[$row] as $col => $oldValue) {
            if ($col >= $from && $col <= $to) {
                $this->set($row, $col, $value);
            }
        }

        self::$disableEvallAll = false;

        self::evaluateAll();
    }

    /**
     * Fill a vertical range
     * 
     * @param mixed $column
     * @param mixed $from
     * @param mixed $to
     * @param mixed $value
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function fillVertical(mixed $column, mixed $from, mixed $to, mixed $value): void
    {
        if (!$this->existsCell($from, $column) || !$this->existsCell($to, $column)) {
            throw new InvalidArgumentException('Invalid range');
        }

        self::$disableEvallAll = true;

        foreach ($this->matrix as $rowIndex => $row) {
            if ($rowIndex >= $from && $rowIndex <= $to) {
                $this->set($rowIndex, $column, $value);
            }
        }

        self::$disableEvallAll = false;

        self::evaluateAll();
    }

    /**
     * Fill a range
     * 
     * @param mixed $rowFrom
     * @param mixed $columnFrom
     * @param mixed $rowTo
     * @param mixed $columnTo
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function fillRange(mixed $rowFrom, mixed $columnFrom, mixed $rowTo, mixed $columnTo, mixed $value): void
    {
        $totalRows = count($this->matrix);

        if ($totalRows == 0) {
            return;
        }

        $totalColumns = count($this->matrix[0]);

        (
            $rowFrom >= 0
            and $rowTo < $totalRows
            and $columnFrom >= 0
            and $columnTo < $totalColumns
            and $rowFrom <= $rowTo
            and $columnFrom <= $columnTo
        ) or throw new InvalidArgumentException('Invalid range');

        self::$disableEvallAll = true;
        foreach ($this->matrix as $rowIndex => $row) {
            if ($rowIndex >= $rowFrom && $rowIndex <= $rowTo) {
                foreach ($row as $colIndex => $oldValue) {
                    if ($colIndex >= $columnFrom && $colIndex <= $columnTo) {
                        $this->set($rowIndex, $colIndex, $value);
                    }
                }
            }
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
    public function eval(mixed $row, mixed $column): void
    {
        $this->validateCoordinates($row, $column);

        if ($this->matrix_original[$row][$column] instanceof Closure) {
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
    public function validateCoordinates(mixed $row, mixed $column): void
    {
        if (!$this->existsCell($row, $column)) {
            throw new InvalidArgumentException('Invalid cell');
        }
    }
    /**
     * Set a value in a cell
     * 
     * @param mixed $row
     * @param mixed $column
     * @param mixed $value
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function set(mixed $row, mixed $column, mixed $value): void
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
    public function get(mixed $row, mixed $column)
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
                if ($col instanceof Closure) {
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
     * @param mixed $row
     * @param mixed $from
     * @param mixed $to
     * 
     * @throws InvalidArgumentException
     * 
     * @return array<mixed>
     */
    public function horizontal(mixed $row, mixed $from = null, mixed $to = null): array
    {

        if (!$this->existsRow($row)) {
            throw new InvalidArgumentException('Invalid row');
        }

        if ((is_int($from) && $from < 0) || $from === null) {
            $from = array_key_first($this->matrix[$row]);
        }

        if ((is_int($to) && $to < 0) || $to === null) {
            $to = array_key_last($this->matrix[$row]);
        }

        $result = [];
        foreach ($this->matrix[$row] as $colIndex => $value) {
            if ($colIndex >= $from && $colIndex <= $to) {
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * Get a vertical range
     * 
     * @param mixed $column
     * @param mixed $from
     * @param mixed $to
     * 
     * @return array<mixed>
     */
    public function vertical(mixed $column, mixed $from = null, mixed $to = null): array
    {
        $result = [];

        if ((is_int($from) && $from < 0) || $from === null) {
            $from = array_key_first($this->matrix);
        }

        if ((is_int($to) && $to < 0) || $to === null) {
            $to = array_key_last($this->matrix);
        }

        foreach ($this->matrix as $rowIndex => $row) {
            if ($rowIndex >= $from && $rowIndex <= $to && isset($row[$column])) {
                $result[] = $row[$column];
            }
        }

        return $result;
    }

    /**
     * Get a range
     * 
     * @param mixed $rowFrom
     * @param mixed $columnFrom
     * @param mixed $rowTo
     * @param mixed $columnTo
     * 
     * @throws InvalidArgumentException
     * 
     * @return array<array<mixed>>
     */
    public function range(mixed $rowFrom, mixed $columnFrom, mixed $rowTo, mixed $columnTo, bool $sameIndexes = true): array
    {
        $result = [];
        foreach ($this->matrix as $rowIndex => $row) {
            $resultRow = [];
            if ($rowIndex >= $rowFrom && $rowIndex <= $rowTo) {
                foreach ($row as $colIndex => $col) {
                    if ($colIndex >= $columnFrom && $colIndex <= $columnTo) {
                        if ($sameIndexes) {
                            $resultRow[$colIndex] = $col;
                        } else {
                            $resultRow[] = $col;
                        }
                    }
                }
            }

            if (!empty($resultRow)) {
                if ($sameIndexes) {
                    $result[$rowIndex] = $resultRow;
                } else {
                    $result[] = $resultRow;
                }
            }
        }

        return $result;
    }

    /**
     * Group by a column
     * 
     * @param array<string> $columns
     * @param Closure $aggregate
     * @param bool $firstRowAsHeaders
     * 
     * @return array<array<array<mixed>>>
     */
    public function groupBy(array $columns, Closure $aggregate = null, bool $firstRowAsHeaders = false): array
    {
        $data = $this->matrix;

        $result = [];
        if ($firstRowAsHeaders) {
            array_shift($data);
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

    /**
     * Get the total rows
     * 
     * @return int
     */
    public function getTotalRows(): int
    {
        return count($this->matrix);
    }

    /**
     * Get the total columns
     * 
     * @return int
     */
    public function getTotalColumns(): int
    {
        return count($this->matrix[0]);
    }

    /**
     * Get a row
     * 
     * @param int $row
     * 
     * @return array<mixed>
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
     * @return array<mixed>
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
     * @return array<mixed>
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
     * @return array<mixed>
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

    /**
     * Magic method to get a property
     * 
     * @param string $name
     * 
     * @return mixed
     */
    public function __get($name): mixed
    {
        if ($name == "rows") {
            return $this->getTotalRows();
        }

        if ($name == "columns") {
            return $this->getTotalColumns();
        }

        if (is_numeric($name)) {
            $pos = $name * 1;
            $name = explode(".", $name);
            $row = intval($name[0]);
            $column = intval($name[1] ?? 0);

            if ($pos < 0) {
                $row = $this->getTotalRows() + $row;
            }

            return $this->get($row, $column);
        }

        if (is_string($name)) {
            $name = explode(".", $name);
            if (count($name) == 2) {
                $row = $name[0];
                $column = $name[1];

                return $this->get($row, $column);
            }
        }

        if (property_exists($this, $name)) {
            return $this->$name;
        }

        throw new InvalidArgumentException('Invalid property');
    }

    /**
     * Magic method to set a property
     * 
     * @param string $name
     * @param mixed $value
     * 
     * @return void
     */
    public function __set($name, $value): void
    {
        if (is_numeric($name)) {
            $pos = $name * 1;
            $name = explode(".", $name);
            $row = intval($name[0]);
            $column = intval($name[1] ?? 0);

            if ($pos < 0) {
                $row = $this->getTotalRows() + $row;
            }

            $this->set($row, $column, $value);
            return;
        }

        if (property_exists($this, $name)) {
            $this->$name = $value;
            return;
        }

        if (is_string($name)) {
            $name = explode(".", $name);
            if (count($name) == 2) {
                $row = $name[0];
                $column = $name[1];

                if (is_numeric($row)) $row = intval($row);
                if (is_numeric($column)) $column = intval($column);

                $this->set($row, $column, $value);
                return;
            }
        }

        throw new InvalidArgumentException('Invalid property');
    }

    /**
     * Insert a row before a row
     * 
     * @param int $row
     * @param array<mixed> $data
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function insertBeforeRow(int $row, array $data): void
    {
        if (!$this->validateRow($data)) {
            throw new InvalidArgumentException('Row does not have the same number of elements as the first row');
        }

        array_splice($this->matrix, $row, 0, [$data]);
        array_splice($this->matrix_original, $row, 0, [$data]);

        self::evaluateAll();
    }

    /**
     * Insert a row after a row
     * 
     * @param int $row
     * @param array<mixed> $data
     * 
     * @throws InvalidArgumentException
     * 
     * @return void
     */
    public function insertAfterRow(int $row, array $data): void
    {
        if (!$this->validateRow($data)) {
            throw new InvalidArgumentException('Row does not have the same number of elements as the first row');
        }

        array_splice($this->matrix, $row + 1, 0, [$data]);
        array_splice($this->matrix_original, $row + 1, 0, [$data]);

        self::evaluateAll();
    }

    /**
     * Insert a column before a column
     * 
     * @param int $column
     * @param mixed $value
     * 
     * @return void
     */
    public function insertBeforeColumn(int $column, mixed $value = null): void
    {
        if (!is_array($value)) {
            $value = array_fill(0, count($this->matrix), $value);
            $value = (array) $value;
        }

        $i = 0;
        foreach ($this->matrix as $rowIndex => $row) {
            array_splice($this->matrix[$rowIndex], $column, 0,  [$value[$i]]);
            array_splice($this->matrix_original[$rowIndex], $column, 0,  [$value[$i]]);
            $i++;
        }

        self::evaluateAll();
    }

    /**
     * Insert a column after a column
     * 
     * @param int $column
     * @param mixed $value
     * 
     * @return void
     */
    public function insertAfterColumn(int $column, $value = null): void
    {
        if (!is_array($value)) {
            $value = array_fill(0, count($this->matrix), $value);
            $value = (array) $value;
        }

        $i = 0;
        foreach ($this->matrix as $rowIndex => $row) {
            array_splice($this->matrix[$rowIndex], $column + 1, 0, [$value[$i]]);
            array_splice($this->matrix_original[$rowIndex], $column + 1, 0, [$value[$i]]);
            $i++;
        }

        self::evaluateAll();
    }

    /**
     * Remove a column
     * 
     * @param int $column
     * 
     * @return void
     */
    public function removeColumn(int $column): void
    {
        foreach ($this->matrix as $rowIndex => $row) {
            array_splice($this->matrix[$rowIndex], $column, 1);
            array_splice($this->matrix_original[$rowIndex], $column, 1);
        }

        self::evaluateAll();
    }

    /**
     * Remove a column
     * 
     * @param int $from
     * @param int $to
     * 
     * @return void
     */
    public function removeColumnRange(int $from, int $to): void
    {
        foreach ($this->matrix as $rowIndex => $row) {
            array_splice($this->matrix[$rowIndex], $from, $to - $from + 1);
            array_splice($this->matrix_original[$rowIndex], $from, $to - $from + 1);
        }

        self::evaluateAll();
    }

    /**
     * Remove a row
     * 
     * @param int $from
     * @param int $to
     * 
     * @return void
     */
    public function removeRowRange(int $from, int $to): void
    {
        array_splice($this->matrix, $from, $to - $from + 1);
        array_splice($this->matrix_original, $from, $to - $from + 1);

        self::evaluateAll();
    }

    /**
     * Get the first row index
     * 
     * @return mixed
     */
    public function getFirstRowIndex(): mixed
    {
        return array_key_first($this->matrix_original);
    }

    /**
     * Get the last row index
     * 
     * @return mixed
     */
    public function getLastRowIndex(): mixed
    {
        return array_key_last($this->matrix_original);
    }

    /**
     * Check if a row exists
     * 
     * @param mixed $row
     * 
     * @return bool
     */
    public function existsRow(mixed $row): bool
    {
        return array_key_exists($row, $this->matrix_original);
    }

    /**
     * Check if a column exists
     * 
     * @param mixed $column
     * 
     * @return bool
     */
    public function existsColumn(mixed $column): bool
    {
        $firstRowIndex = array_key_first($this->matrix_original);
        
        if ($firstRowIndex === null) {
            return false;
        }

        return array_key_exists($column, $this->matrix_original[$firstRowIndex]);
    }

    /**
     * Check if a cell exists
     * 
     * @param mixed $row
     * @param mixed $column
     * 
     * @return bool
     */
    public function existsCell(mixed $row, mixed $column): bool
    {
        // IMPORTANT: dont use isset because it returns false for null values
        // and dont use existsColumn and existsRow because they are not as fast as this
        // and maybe the cell exists and the row or column does not exist

        if ($this->existsRow($row))
        {
            return array_key_exists($column, $this->matrix_original[$row]);
        }

        return false;
    }

    /**
     * Get the first column index
     * 
     * @param mixed $row
     * @param mixed $column
     * 
     * @return void
     */
    public function renameColumn(mixed $column, mixed $name): void
    {
        foreach($this->matrix as $rowIndex => $row)
        {
            if (isset($row[$column]))
            {
                $this->matrix[$rowIndex][$name] = $row[$column];
                unset($this->matrix[$rowIndex][$column]);
            }
        }
    }
}
