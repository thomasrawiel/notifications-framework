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

    protected function invokeEventAction(AbstractEvent $event)
    {
        if (!$GLOBALS['BE_USER']->isAdmin() && !$GLOBALS['BE_USER']->check('tables_modify', Configuration::TABLE_NAME)) {
            // not allowed to create notifications
            return;
        }
        $recordId = $event->getId();
        $table = $event->getTable();

        //if we're updating an existing default to a record config, we need to write the table name
        if ($table === Configuration::TABLE_NAME && !str_starts_with((string)$recordId, 'NEW')) {
            $recordFieldArray = BackendUtility::getRecord($table, $recordId, 'type,record,table');

            if (Type::isRecordType($recordFieldArray['type']) && !empty($recordFieldArray['record']) && !str_starts_with($recordFieldArray['record'], $recordFieldArray['table'] . '_')) {
                $data[Configuration::TABLE_NAME][$recordId] = [
                    'table' => preg_replace('/_\d+$/', '', $recordFieldArray['record']),
                ];
                $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
                $dataHandler->start($data, []);
                $dataHandler->process_datamap();
            }
            return;
        }

        if (!in_array($table, $this->settingsUtility->getAllowedTables())) {
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
        $dataEvent = $eventDispatcher->dispatch(new BeforeConfigurationAddedEvent($data, $event));

        if ($dataEvent->isAddConfiguration()) {
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->start($dataEvent->getData(), []);
            $dataHandler->process_datamap();
        }
    }
}
