<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Helpers\Array\ArrayHelper;
use Config\Database;

/**
 * Validation Rules.
 *
 * @see RulesTest
 */
class Rules
{
    /**
     * The value does not match another field in $data.
     *
     * @param null|string $str
     * @param array       $data Other field/value pairs
     */
    public function differs($str, string $field, array $data): bool
    {
        if (str_contains($field, '.')) {
            return $str !== dot_array_search($field, $data);
        }

        return array_key_exists($field, $data) && $str !== $data[$field];
    }

    /**
     * Equals the static value provided.
     *
     * @param null|string $str
     */
    public function equals($str, string $val): bool
    {
        if (!is_string($str) && null !== $str) {
            $str = (string) $str;
        }

        return $str === $val;
    }

    /**
     * Returns true if $str is $val characters long.
     * $val = "5" (one) | "5,8,12" (multiple values).
     *
     * @param null|string $str
     */
    public function exact_length($str, string $val): bool
    {
        if (!is_string($str) && null !== $str) {
            $str = (string) $str;
        }

        $val = explode(',', $val);

        foreach ($val as $tmp) {
            if (is_numeric($tmp) && (int) $tmp === mb_strlen($str ?? '')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Greater than.
     *
     * @param null|string $str
     */
    public function greater_than($str, string $min): bool
    {
        if (!is_string($str) && null !== $str) {
            $str = (string) $str;
        }

        return is_numeric($str) && $str > $min;
    }

    /**
     * Equal to or Greater than.
     *
     * @param null|string $str
     */
    public function greater_than_equal_to($str, string $min): bool
    {
        if (!is_string($str) && null !== $str) {
            $str = (string) $str;
        }

        return is_numeric($str) && $str >= $min;
    }

    /**
     * Checks the database to see if the given value exist.
     * Can ignore records by field/value to filter (currently
     * accept only one filter).
     *
     * Example:
     *    is_not_unique[dbGroup.table.field,where_field,where_value]
     *    is_not_unique[table.field,where_field,where_value]
     *    is_not_unique[menu.id,active,1]
     *
     * @param null|string $str
     */
    public function is_not_unique($str, string $field, array $data): bool
    {
        [$builder, $whereField, $whereValue] = $this->prepareUniqueQuery($str, $field, $data);

        if (
            null !== $whereField && '' !== $whereField
            && null !== $whereValue && '' !== $whereValue
            && 1 !== preg_match('/^\{(\w+)\}$/', $whereValue)
        ) {
            $builder = $builder->where($whereField, $whereValue);
        }

        return null !== $builder->get()->getRow();
    }

    /**
     * Value should be within an array of values.
     *
     * @param null|string $value
     */
    public function in_list($value, string $list): bool
    {
        if (!is_string($value) && null !== $value) {
            $value = (string) $value;
        }

        $list = array_map(trim(...), explode(',', $list));

        return in_array($value, $list, true);
    }

    /**
     * Checks the database to see if the given value is unique. Can
     * ignore a single record by field/value to make it useful during
     * record updates.
     *
     * Example:
     *    is_unique[dbGroup.table.field,ignore_field,ignore_value]
     *    is_unique[table.field,ignore_field,ignore_value]
     *    is_unique[users.email,id,5]
     *
     * @param null|string $str
     */
    public function is_unique($str, string $field, array $data): bool
    {
        [$builder, $ignoreField, $ignoreValue] = $this->prepareUniqueQuery($str, $field, $data);

        if (
            null !== $ignoreField && '' !== $ignoreField
            && null !== $ignoreValue && '' !== $ignoreValue
            && 1 !== preg_match('/^\{(\w+)\}$/', $ignoreValue)
        ) {
            $builder = $builder->where("{$ignoreField} !=", $ignoreValue);
        }

        return null === $builder->get()->getRow();
    }

    /**
     * Less than.
     *
     * @param null|string $str
     */
    public function less_than($str, string $max): bool
    {
        if (!is_string($str) && null !== $str) {
            $str = (string) $str;
        }

        return is_numeric($str) && $str < $max;
    }

    /**
     * Equal to or Less than.
     *
     * @param null|string $str
     */
    public function less_than_equal_to($str, string $max): bool
    {
        if (!is_string($str) && null !== $str) {
            $str = (string) $str;
        }

        return is_numeric($str) && $str <= $max;
    }

    /**
     * Matches the value of another field in $data.
     *
     * @param null|string $str
     * @param array       $data Other field/value pairs
     */
    public function matches($str, string $field, array $data): bool
    {
        if (str_contains($field, '.')) {
            return $str === dot_array_search($field, $data);
        }

        return isset($data[$field]) && $str === $data[$field];
    }

    /**
     * Returns true if $str is $val or fewer characters in length.
     *
     * @param null|string $str
     */
    public function max_length($str, string $val): bool
    {
        if (!is_string($str) && null !== $str) {
            $str = (string) $str;
        }

        return is_numeric($val) && $val >= mb_strlen($str ?? '');
    }

    /**
     * Returns true if $str is at least $val length.
     *
     * @param null|string $str
     */
    public function min_length($str, string $val): bool
    {
        if (!is_string($str) && null !== $str) {
            $str = (string) $str;
        }

        return is_numeric($val) && $val <= mb_strlen($str ?? '');
    }

    /**
     * Does not equal the static value provided.
     *
     * @param null|string $str
     */
    public function not_equals($str, string $val): bool
    {
        if (!is_string($str) && null !== $str) {
            $str = (string) $str;
        }

        return $str !== $val;
    }

    /**
     * Value should not be within an array of values.
     *
     * @param null|string $value
     */
    public function not_in_list($value, string $list): bool
    {
        if (!is_string($value) && null !== $value) {
            $value = (string) $value;
        }

        return !$this->in_list($value, $list);
    }

    /**
     * @param null|array|bool|float|int|object|string $str
     */
    public function required($str = null): bool
    {
        if (null === $str) {
            return false;
        }

        if (is_object($str)) {
            return true;
        }

        if (is_array($str)) {
            return [] !== $str;
        }

        return '' !== trim((string) $str);
    }

    /**
     * The field is required when any of the other required fields are present
     * in the data.
     *
     * Example (field is required when the password field is present):
     *
     *     required_with[password]
     *
     * @param null|string $str
     * @param null|string $fields List of fields that we should check if present
     * @param array       $data   Complete list of fields from the form
     */
    public function required_with($str = null, ?string $fields = null, array $data = []): bool
    {
        if (null === $fields || [] === $data) {
            throw new InvalidArgumentException('You must supply the parameters: fields, data.');
        }

        // If the field is present we can safely assume that
        // the field is here, no matter whether the corresponding
        // search field is present or not.
        $present = $this->required($str ?? '');

        if ($present) {
            return true;
        }

        // Still here? Then we fail this test if
        // any of the fields are present in $data
        // as $fields is the list
        $requiredFields = [];

        foreach (explode(',', $fields) as $field) {
            if (
                (array_key_exists($field, $data) && !empty($data[$field]))
                || (str_contains($field, '.') && !empty(dot_array_search($field, $data)))
            ) {
                $requiredFields[] = $field;
            }
        }

        return [] === $requiredFields;
    }

    /**
     * The field is required when all the other fields are present
     * in the data but not required.
     *
     * Example (field is required when the id or email field is missing):
     *
     *     required_without[id,email]
     *
     * @param null|string $str
     * @param null|string $otherFields the param fields of required_without[]
     * @param null|string $field       this rule param fields aren't present, this field is required
     */
    public function required_without(
        $str = null,
        ?string $otherFields = null,
        array $data = [],
        ?string $error = null,
        ?string $field = null,
    ): bool {
        if (null === $otherFields || [] === $data) {
            throw new InvalidArgumentException('You must supply the parameters: otherFields, data.');
        }

        // If the field is present we can safely assume that
        // the field is here, no matter whether the corresponding
        // search field is present or not.
        $present = $this->required($str ?? '');

        if ($present) {
            return true;
        }

        // Still here? Then we fail this test if
        // any of the fields are not present in $data
        foreach (explode(',', $otherFields) as $otherField) {
            if (
                (!str_contains($otherField, '.'))
                && (!array_key_exists($otherField, $data)
                    || empty($data[$otherField]))
            ) {
                return false;
            }

            if (str_contains($otherField, '.')) {
                if (null === $field) {
                    throw new InvalidArgumentException('You must supply the parameters: field.');
                }

                $fieldData = dot_array_search($otherField, $data);
                $fieldSplitArray = explode('.', $field);
                $fieldKey = $fieldSplitArray[1];

                if (is_array($fieldData)) {
                    return !empty(dot_array_search($otherField, $data)[$fieldKey]);
                }
                $nowField = str_replace('*', $fieldKey, $otherField);
                $nowFieldVaule = dot_array_search($nowField, $data);

                return null !== $nowFieldVaule;
            }
        }

        return true;
    }

    /**
     * The field exists in $data.
     *
     * @param null|array|bool|float|int|object|string $value the field value
     * @param null|string                             $param the rule's parameter
     * @param array                                   $data  the data to be validated
     * @param null|string                             $field the field name
     */
    public function field_exists(
        $value = null,
        ?string $param = null,
        array $data = [],
        ?string $error = null,
        ?string $field = null,
    ): bool {
        if (str_contains($field, '.')) {
            return ArrayHelper::dotKeyExists($field, $data);
        }

        return array_key_exists($field, $data);
    }

    /**
     * Prepares the database query for uniqueness checks.
     *
     * @param mixed                $value the value to check
     * @param string               $field the field parameters
     * @param array<string, mixed> $data  additional data
     *
     * @return array{0: BaseBuilder, 1: null|string, 2: null|string}
     */
    private function prepareUniqueQuery($value, string $field, array $data): array
    {
        if (!is_string($value) && null !== $value) {
            $value = (string) $value;
        }

        // Split the field parameters and pad the array to ensure three elements.
        [$field, $extraField, $extraValue] = array_pad(explode(',', $field), 3, null);

        // Parse the field string to extract dbGroup, table, and field.
        $parts = explode('.', $field, 3);
        $numParts = count($parts);

        if (3 === $numParts) {
            [$dbGroup, $table, $field] = $parts;
        } elseif (2 === $numParts) {
            [$table, $field] = $parts;
        } else {
            throw new InvalidArgumentException('The field must be in the format "table.field" or "dbGroup.table.field".');
        }

        // Connect to the database.
        $dbGroup ??= $data['DBGroup'] ?? null;
        $builder = Database::connect($dbGroup)
            ->table($table)
            ->select('1')
            ->where($field, $value)
            ->limit(1)
        ;

        return [$builder, $extraField, $extraValue];
    }
}
