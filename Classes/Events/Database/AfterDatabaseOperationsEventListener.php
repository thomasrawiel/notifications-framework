<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Database;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Events\AbstractEvent;
use TRAW\NotificationsFramework\Events\AbstractEventListener;
use TRAW\NotificationsFramework\Events\Configuration\BeforeConfigurationAddedEvent;
use TRAW\NotificationsFramework\Events\Configuration\RecordAllowedEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;

/**
 * Class AfterDatabaseOperationsEventListener
 */
#[AsEventListener(
    identifier: 'traw-notifications/database',
)]
final class AfterDatabaseOperationsEventListener extends AbstractEventListener
{
    /**
     * @var string
     */
    protected string $expectedEventClass = AfterDatabaseOperationsEvent::class;

    public function __construct(private readonly Type $type, private readonly CacheManager $cacheManager)
    {
    }

    protected function invokeEventAction(AbstractEvent $event)
    {
        if (!$GLOBALS['BE_USER']->isAdmin() && !$GLOBALS['BE_USER']->check('tables_modify', Configuration::TABLE_NAME)) {
            // not allowed to create notifications
            return;
        }

        $recordId = $event->getId();
        $table = $event->getTable();
        $record = BackendUtility::getRecord($table, $recordId);

        if ($event->getStatus() === 'update') {
            if ($table === Configuration::TABLE_NAME) {
                $this->cacheManager->flushCachesByTag('tx_notifications_framework_validation_record_' . $recordId);
                $this->cacheManager->flushCachesByTag('tx_notifications_framework_audience_record_' . $recordId);
            } else {
                $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable(Configuration::TABLE_NAME);
                $qb->getRestrictions()->removeAll();
                $linkedConfigurations = $qb->select('uid')
                    ->from(Configuration::TABLE_NAME)
                    ->where(
                        $qb->expr()->eq('record', $qb->createNamedParameter($table.'_'.$recordId))
                    )->execute()->fetchAllAssociative();

                foreach ($linkedConfigurations as $c) {
                    $this->cacheManager->flushCachesByTag('tx_notifications_framework_validation_record_' . $c['uid']);
                    $this->cacheManager->flushCachesByTag('tx_notifications_framework_audience_record_' . $c['uid']);
                }
            }

        }
        //if we're updating an existing default to a record config, we need to write the table name
        if ($table === Configuration::TABLE_NAME && !str_starts_with((string)$recordId, 'NEW')) {
            if ($this->type->isRecordType($record['type']) && !empty($record['record']) && !str_starts_with($record['record'], $record['table'] . '_')) {
                $data[Configuration::TABLE_NAME][$recordId] = [
                    'table' => preg_replace('/_\d+$/', '', $record['record']),
                ];
                $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $dataHandler->start($data, []);
                $dataHandler->process_datamap();
            }
            return;
        }

        if (!in_array($table, $this->settingsUtility->getAllowedTables(), true)) {
            return;
        }

        //we dont need translations for record configurations, we translate the notifications in the Generate command
        if (($record['sys_language_uid'] ?? false) !== 0) {
            return;
        }

        $recordFieldArray = $event->getFieldArray();
        if ($this->settingsUtility->automaticallyCreateNotifications() === false && (bool)($recordFieldArray['notification_create'] ?? true) === false) {
            return;
        }

        $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $recordAllowedEvent = $eventDispatcher->dispatch(new RecordAllowedEvent($table, $recordId, $recordFieldArray, $event->getDataHandler()));
        if (!$recordAllowedEvent->isRecordAllowed()) {
            return;
        }

        $createNotificationConfiguration = (bool)($recordFieldArray['notification_create'] ?? true);
        if ($event->getStatus() === 'update' && MathUtility::canBeInterpretedAsInteger($recordId)) {
            //if we update a record, we check if the notification_create field has changed
            $history = $event->getDataHandler()->getHistoryRecords()[$table . ':' . $recordId];
            $createNotificationConfiguration = (bool)($history['newRecord']['notification_create'] ?? false);
        }

        if (!isset($GLOBALS['TCA'][$table]['columns']['notification_create'])) {
            //if automatic=1, table-allowed=1 but field is missing, create it anyways but only when it's a new record
            $createNotificationConfiguration = $event->getStatus() === 'new';
        }

        if ($createNotificationConfiguration === false) {
            return;
        }

        $pid = $recordFieldArray['pid'] ?? false;
        if ($pid === false && $event->getStatus() === 'update') {
            if ($this->settingsUtility->storeNotificationsOnRecordPid()) {
                $pid = BackendUtility::getRecord($table, $recordId, 'pid')['pid'] ?? 0;
            } else {
                $pid = $this->settingsUtility->getNotificationStorage();
            }
        }

        $newId = \TYPO3\CMS\Core\Utility\StringUtility::getUniqueId('NEW');
        $data[Configuration::TABLE_NAME][$newId] = [
            'type' => $event->getStatus() === 'new' ? Type::RECORDADDED : Type::RECORDUPDATED,
            'pid' => $pid,
            'table' => $table,
            'title' => ($event->getStatus() === 'new' ? Type::RECORDADDED : Type::RECORDUPDATED) . ' in ' . $event->getTable(),
            'label' => BackendUtility::getRecord($table, $recordId, 'title')['title'] ?? ($event->getStatus() === 'new' ? Type::RECORDADDED : Type::RECORDUPDATED) . 'with ID ' . $recordId,
            'message' => $event->getStatus() === 'new' ? Type::RECORDADDED : Type::RECORDUPDATED,
            'record' => $event->getRecordIdentifier(),
            'automatic' => 1,
            'rowDescription' => 'Automatically created by ' . basename(self::class),
        ];

        $feGroupField = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['fe_group'] ?? false;
        if ($feGroupField) {
            $feGroups = BackendUtility::getRecord($table, $recordId, $feGroupField)[$feGroupField] ?? null;

            if (!empty($feGroups)) {
                $data[Configuration::TABLE_NAME][$newId]['target_audience'] = 'groups';
                $data[Configuration::TABLE_NAME][$newId]['fe_groups'] = $feGroups;
            }
        }
        $dataEvent = $eventDispatcher->dispatch(new BeforeConfigurationAddedEvent($newId, $data, $event));

        if ($dataEvent->isAddConfiguration()) {
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->start($dataEvent->getData(), []);
            $dataHandler->process_datamap();
        }
    }
}
