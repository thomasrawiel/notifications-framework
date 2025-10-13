<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TRAW\NotificationsFramework\Events\Configuration\AllowedTablesEvent;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class SettingsUtility
{
    public function __construct(private mixed $config = [])
    {
        $extConf = GeneralUtility::makeInstance(ExtensionConfiguration::class);
        $this->config = $extConf->get('notifications_framework');
    }

    public function getAllowedTables(): array
    {
        $dispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $allowedTablesEvent = new AllowedTablesEvent(GeneralUtility::trimExplode(',', $this->config['allowedTables'], true));

        return $dispatcher->dispatch($allowedTablesEvent)->getAllowedTables();
    }

    public function getAllowedTablesList(): string
    {
        return implode(',', $this->getAllowedTables());
    }

    public function automaticallyCreateNotifications(): bool
    {
        return (bool)$this->config['autoCreateNotifications'];
    }
}
