<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SettingsUtility
{
    private $config;

    public function __construct()
    {
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $this->config = $extConf->get('notifications_framework');
    }


    public function getAllowedTables(): array
    {
        $tables = array_filter(explode(',', $this->config['allowedTables']), function ($e) {
            return $e !== 'tx_notifications_framework_configuration';
        });

        return $tables;
    }
}