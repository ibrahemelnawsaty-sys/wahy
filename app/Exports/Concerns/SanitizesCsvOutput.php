<?php

namespace App\Exports\Concerns;

trait SanitizesCsvOutput
{
    /**
     * Neutralize CSV/formula injection for a single cell value.
     *
     * If the value is a string whose first non-whitespace character is one of
     * = + - @ \t \r, prefix it with a single quote so spreadsheet apps treat
     * it as literal text instead of a formula.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function sanitizeCsv($value)
    {
        if (is_string($value) && preg_match('/^[\s]*[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Run every value of a row through sanitizeCsv().
     */
    protected function sanitizeRow(array $row): array
    {
        return array_map([$this, 'sanitizeCsv'], $row);
    }
}
