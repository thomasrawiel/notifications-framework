<?php
$settingsUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TRAW\NotificationsFramework\Utility\SettingsUtility::class);

if (in_array('pages', $settingsUtility->getAllowedTables())
    && $settingsUtility->automaticallyCreateNotifications()) {
    $defaultTca = \TRAW\NotificationsFramework\Domain\DTO\Notification::defaultTCA;
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('pages', $defaultTca);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette('pages', 'visibility', implode(',', array_keys($defaultTca)));
}
