<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;
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
     * Encodes an array of checked checkboxes into a bitmask.
     *
     * @param array $checkedValues Array of either bit indexes (e.g. [0, 2]) or bit values (e.g. [1, 4])
     * @param bool  $useValues     If true, input is treated as bit values (1, 2, 4, ...). If false, as bit indexes (0,
     *                             1, 2).
     *
     * @return int Bitmask result
     */
    public static function encodeCheckboxes(array $checkedValues, bool $useValues = false): int
    {
        $bitmask = 0;

        foreach ($checkedValues as $val) {
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