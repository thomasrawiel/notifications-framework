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

    public function getSettings(): array {
        return $this->config;
    }

    public function automaticallyCreateNotifications(): bool
    {
        return (bool)$this->config['autoCreateNotifications'];
    }

    public function sendToEveryoneIfNoAudienceIsSelected(): bool
    {
        return (bool)$this->config['sendToEveryoneIfNoAudienceIsSelected'];
    }

    public function getFeUserLookupUids(): array
    {
        return array_map('intval', GeneralUtility::trimExplode(',', $this->config['feUserLookupUids'], true));
    }

    public function getFeUserLookupRecursive(): int
    {
        return (int)$this->config['feUserLookupRecursive'];
    }

    public function storeNotificationsOnRecordPid(): bool
    {
        return (bool)($this->config['storeNotificationsOnRecordPid'] ?? true);
    }

    public function getNotificationStorage(): array
    {
        return array_map('intval', GeneralUtility::trimExplode(',', $this->config['notificationStorage'], true));
    }

    public function getNotificationStorageRecursive(): int
    {
        return (int)$this->config['notificationStorageRecursive'];
    }

    public function checkPid(string|int|null $pid): int
    {
        return $this->storeNotificationsOnRecordPid()
            ? (int)$pid
            : $this->getNotificationStorage()[0];
    }

    public function isPidValid(string|int|null $pid): bool
    {
        if ($this->storeNotificationsOnRecordPid()) {
            return true;
        }
        $treeListUtility = GeneralUtility::makeInstance(TreeListUtility::class);

        $treeList = $treeListUtility->getTreeListArrayFromArray($this->getNotificationStorage(), $this->getNotificationStorageRecursive());
        return in_array($pid, $treeList);
    }
}
