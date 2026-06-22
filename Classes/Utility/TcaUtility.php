<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TRAW\NotificationsFramework\Domain\DTO\Notification;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class TcaUtility
{
    public static function addTCA(string $table, string $typeList, string $position, string $asPalette): void
    {
        $settingsUtility = GeneralUtility::makeInstance(SettingsUtility::class);

        if (!in_array($table, $settingsUtility->getAllowedTables(), true)) {
            return;
        }

        if (!$settingsUtility->automaticallyCreateNotifications()) {
            return;
        }

        $defaultTca = Notification::defaultTCA;
        $fieldString = implode(',', array_keys($defaultTca));
        ExtensionManagementUtility::addTCAcolumns($table, $defaultTca);
        if (!empty($asPalette)) {
            ExtensionManagementUtility::addFieldsToPalette($table, $asPalette, $fieldString);
        } else {
            $addFieldString = $fieldString;
            ExtensionManagementUtility::addToAllTCAtypes($table, $fieldString, $typeList, $position);
        }
    }
}
