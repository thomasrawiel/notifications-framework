<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Hooks;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Utility\RecordIconOverlayUtility;

/**
 * Class OverrideIconOverlayHook
 */
final class OverrideIconOverlayHook
{
    /**
     * @param string $table
     * @param array  $row
     * @param array  $status
     * @param string $iconName
     *
     * @return string
     */
    public function postOverlayPriorityLookup(string $table, array $row, array &$status, string $iconName): string
    {
        if ($table == Configuration::TABLE_NAME && !empty($row)) {
            $identifier = RecordIconOverlayUtility::getRecordIconOverlay($row);

            if (!empty($identifier)) {
                $iconName = $identifier;
            }
        }

        return $iconName;
    }
}