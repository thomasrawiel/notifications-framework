<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Database;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Events\AbstractEvent;
use TRAW\NotificationsFramework\Events\AbstractEventListener;
use TRAW\NotificationsFramework\Events\Configuration\BeforeConfigurationAddedEvent;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

/**
 * Class AfterDatabaseOperationsEventListener
 */
class AfterDatabaseOperationsEventListener extends AbstractEventListener
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

        if ($this->settingsUtility->automaticallyCreateNotifications() === false) {
            return;
        }

        $table = $event->getTable();
        if ($table === Configuration::TABLE_NAME) {
            return;
        }


        if (in_array($table, $this->settingsUtility->getAllowedTables())) {
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $record = $event->getFieldArray();
            $recordId = $event->getId();

            $createNotification = (bool)($record['notification_create'] ?? true);
            if ($event->getStatus() === 'update' && MathUtility::canBeInterpretedAsInteger($recordId)) {
                $history = $event->getDataHandler()->getHistoryRecords()[$table . ':' . $recordId];
                $createNotification = (bool)($history['newRecord']['notification_create'] ?? false);
            }

            if (!isset($GLOBALS['TCA'][$table]['columns']['notification_create'])) {
                //if automatic=1, table-allowed=1 but field is missing, create it anyways but only when it's a new record
                $createNotification = $event->getStatus() === 'new';
            }

            if ($createNotification === false) {
                return;
            }

            $pid = $record['pid'] ?? 0;
            if ($pid === 0 && $event->getStatus() === 'update') {
                $pid = BackendUtility::getRecord($table, $recordId, 'pid')['pid'] ?? 0;
            }

            $data[Configuration::TABLE_NAME][\TYPO3\CMS\Core\Utility\StringUtility::getUniqueId('NEW')] = [
                'type' => Type::RECORDADDED,
                'pid' => $pid,
                'table' => $table,
                'title' => 'Record added in ' . $event->getTable(),
                'label' => BackendUtility::getRecordTitle($table, $record),
                'record' => $event->getRecordIdentifier(),
                'rowDescription' => 'Automatically created by ' . basename(self::class),
            ];

            $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
            $dataEvent = $eventDispatcher->dispatch(new BeforeConfigurationAddedEvent($data, $event));

            if ($dataEvent->isAddConfiguration()) {
                $dataHandler->start($dataEvent->getData(), []);
                $dataHandler->process_datamap();
            }
        }
    }
}
