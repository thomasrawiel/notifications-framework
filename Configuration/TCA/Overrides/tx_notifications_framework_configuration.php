<?php
declare(strict_types=1);

$settingsUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TRAW\NotificationsFramework\Utility\SettingsUtility::class);

if ($settingsUtility->isAutoTranslate()) {
    $LLL = 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:';
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns(
        \TRAW\NotificationsFramework\Domain\Model\Configuration::TABLE_NAME,
        [
        'autotranslate' => [
            'l10n_mode' => 'exclude',
            'exclude' => true,
            'label' => $LLL . 'configuration.autotranslate',
            'description' => $LLL . 'configuration.autotranslate.description.'.$settingsUtility->getAutoTranslateMode(),
            'config' => [
                'type' => 'check',
                'renderType' => 'checkboxToggle',
                'items' => [
                    [
                        'label' => '',
                    ],
                ],
                'default' => 1,
            ],
        ],
    ]);
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addFieldsToPalette(
        \TRAW\NotificationsFramework\Domain\Model\Configuration::TABLE_NAME,
        'language',
        '--linebreak--,autotranslate'
    );
}
