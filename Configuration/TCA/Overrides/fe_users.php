<?php
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', [
    'notifications' => [
        'label' => 'Notifications',
        'config' => [
            'type' => 'passthrough',
            'default' => 0,
        ],
    ],
]);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'fe_users',
    'notifications',
);
