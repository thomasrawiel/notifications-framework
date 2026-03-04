<?php
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', [
    'notifications' => [
        'label' => 'Notifications Reference',
        'config' => [
            'type' => 'inline',
            'foreign_table' => 'tx_notifications_framework_domain_model_notification_reference',
            'foreign_field' => 'user',
            'appearance' => [
                'collapseAll' => 1,
                'levelLinksPosition' => 'top',
                'useSortable' => 1,
            ],
        ],
    ],
]);

if(!isset($GLOBALS['TCA']['fe_users']['palettes']['notifications']['label']))
{
    $GLOBALS['TCA']['fe_users']['palettes']['notifications']['label'] = 'Notifications';
}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
    'fe_users',
    'notifications',
    'notifications',
);
