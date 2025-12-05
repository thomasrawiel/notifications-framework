<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class BitmaskUtility
 *
 * We want to store the state of all checkboxes in a single integer value
 *
 * Checkbox 3    Checkbox 2    Checkbox 1    Bitmask (binary)    Decimal
 * ❌                ❌           ❌           000                 0
 * ❌                ❌           ✅           001                 1
 * ❌                ✅           ❌           010                 2
 * ❌                ✅           ✅           011                 3
 * ✅                ❌           ❌           100                 4
 * ✅                ❌           ✅           101                 5
 * ✅                ✅           ❌           110                 6
 * ✅                ✅           ✅           111                 7
 *
 */
class BitmaskUtility
{
    /**
     * Encodes an array of checked checkboxes into a single bitmask integer.
     *
     * Each checked checkbox is represented as a bit in the returned bitmask.
     * Unchecked checkboxes should either be absent from the array or set to null.
     *
     *
     * @param array $checkedValues An array of integers representing the checked checkboxes.
     *                             - In **index mode** ($useValues = false), each value is the **zero-based index** of
     *                             the checked checkbox. Example: [0, 2] → bits 0 and 2 are set → bitmask 0b101 →
     *                             decimal 5.
     *                             - In **value mode** ($useValues = true), each value must be a **power of two** (1,
     *                             2, 4, 8, ...) and is directly OR-ed into the bitmask. Example: [1, 4] → bitmask
     *                             0b101 → decimal 5.
     *                             - Any null values are ignored.
     *                             - In value mode, a value that is **not a power of two** will throw an
     *                             InvalidArgumentException.
     *
     * @param bool  $useValues     Determines how to interpret the input array:
     *                             - false (default): $checkedValues are **indexes** (0-based).
     *                             - true: $checkedValues are **bit values** (powers of two).
     *
     * ### Example Usage:
     *
     * // Index mode (default), 3 or more checkboxes (first and third checked)
     * encodeCheckboxes([0, 2]); // returns 5 (0b101)
     * same as encodeCheckboxes([0, null, 2, null, ...]); // returns 5 (0b101)
     *
     *  // Ignoring nulls, 3 or more checkboxes
     *  encodeCheckboxes([0, null, 2]); // returns 9 (0b1001)
     *  encodeCheckboxes([0, null, 2, null, ...]); // returns 9 (0b1001)
     *
     * // Value mode, 2 or more checkboxes, only the first 2 are checked
     * encodeCheckboxes([1, 4], true); // returns 5 (0b101)
     * same as encodeCheckboxes([1, 4, null, ....], true); // returns 5 (0b101)
     *
     * @return int The resulting bitmask as an integer.
     * @throws \InvalidArgumentException If a value in value mode is not a power of two.
     */
    public static function encodeCheckboxes(string|array $checkedValues, bool $useValues = false): int
    {
        $bitmask = 0;

        if (is_string($checkedValues)) {
            $checkedValues = GeneralUtility::trimExplode(',', $checkedValues, true);
        }

        foreach ($checkedValues as $val) {
            if ($val === null) continue;
            if ($useValues) {
                if (!self::isPowerOfTwo($val)) {
                    throw new InvalidArgumentException("Value $val is not a power of 2.");
                }
                $bitmask |= (int)$val;
            } else {
                // Treat as index, convert to 2^index (same as  pow(2, $index))
                $bitmask |= (1 << (int)$val);
            }
        }

        return $bitmask;
    }

    /**
     * @param int $n
     *
     * @return bool
     */
    private static function isPowerOfTwo(int $n): bool
    {
        return $n > 0 && ($n & ($n - 1)) === 0;
    }

    /**
     * Decodes a bitmask into checkbox states.
     *
     * @param int  $bitmask         The stored bitmask value representing checked checkboxes.
     * @param int  $totalCheckboxes Total number of checkboxes (i.e., number of bits to evaluate).
     * @param bool $returnAll       If true, returns all checkbox states (true/false). If false, returns only the
     *                              checked ones.
     * @param bool $returnValues    If true, returns bit values (e.g., 1, 2, 4) instead of checkbox indexes (0, 1, 2).
     *                              Applies to both keys (when $returnAll is true) and values (when false).
     *
     * @return array An array of checkbox states:
     *               - If $returnAll is true: [index/value => bool]
     *               - If $returnAll is false: [index/value, index/value, ...] only for checked checkboxes
     */
    public static function decodeCheckboxes(int $bitmask, int $totalCheckboxes, bool $returnAll = true, bool $returnValues = false): array
    {
        $checked = [];

        for ($i = 0; $i < $totalCheckboxes; $i++) {
            $value = 1 << $i;
            $isChecked = ($bitmask & $value) !== 0;

            if ($returnAll) {
                $key = $returnValues ? $value : $i;
                $checked[$key] = $isChecked;
            } elseif ($isChecked) {
                $checked[] = $returnValues ? $value : $i;
            }
        }

        return $checked;
    }

    /**
     * Check if the N-th checkbox is checked.
     *
     * @param int $bitmask       The current bitmask
     * @param int $checkboxIndex Zero-based index (e.g. 2 for third checkbox)
     *
     * @return bool True if checked, false otherwise
     */
    public static function isCheckboxChecked(int $bitmask, int $checkboxIndex): bool
    {
        return ($bitmask & (1 << $checkboxIndex)) !== 0;
    }

}
