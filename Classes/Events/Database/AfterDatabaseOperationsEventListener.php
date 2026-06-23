<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Database;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Events\AbstractEvent;
use TRAW\NotificationsFramework\Events\AbstractEventListener;
use TRAW\NotificationsFramework\Events\Configuration\BeforeConfigurationAddedEvent;
use TRAW\NotificationsFramework\Events\Configuration\RecordAllowedEvent;
use TRAW\NotificationsFramework\Service\RateLimitService;
use TRAW\NotificationsFramework\Utility\LanguageUtility;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageQueue;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Utility\StringUtility;

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

    public function __construct(
        private readonly Type                    $type,
        private readonly CacheManager            $cacheManager,
        private readonly ConfigurationRepository $configurationRepository,
        private readonly RateLimitService        $rateLimitService,
    )
    {
    }

    protected function invokeEventAction(AbstractEvent $event)
    {
        if (!$GLOBALS['BE_USER']->isAdmin() && !$GLOBALS['BE_USER']->check('tables_modify', Configuration::TABLE_NAME)) {
            return;
        }

        $recordId = $event->getId();
        $table = $event->getTable();

        if ($event->getStatus() === 'update') {
            if ($table === Configuration::TABLE_NAME) {
                $uids = [$recordId];
            } elseif (in_array($table, $this->settingsUtility->getAllowedTables(), true)) {
                $uids = array_column($this->configurationRepository->getConfigurationsByDemand(['record' => $table . '_' . $recordId]), 'uid');
            } else {
                return;
            }
            foreach ($uids as $uid) {
                $this->cacheManager->flushCachesByTag('tx_notifications_framework_validation_record_' . $uid);
                $this->cacheManager->flushCachesByTag('tx_notifications_framework_audience_record_' . $uid);
            }
        }

        if (!in_array($table, $this->settingsUtility->getAllowedTables(), true)) {
            return;
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

        $recordFieldArray = $event->getFieldArray();
        if ($this->settingsUtility->automaticallyCreateNotifications() === false && (bool)($recordFieldArray['notification_create'] ?? true) === false) {
            return;
        }

        $record = BackendUtility::getRecord($table, $recordId);
        //we dont need translations for record configurations, we translate the notifications in the Generate command
        if (!in_array(($record['sys_language_uid'] ?? null), [0, -1])) {
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

        $pid = $this->settingsUtility->checkPid(
            $recordFieldArray['pid'] ?? $record['pid'] ?? 0
        );

        $newId = StringUtility::getUniqueId('NEW');
        $recordTypeTitle = $GLOBALS['TCA'][$table]['ctrl']['title'] ?? null;
        if ($recordTypeTitle !== null) {
            $recordTypeTitle = LanguageUtility::translate($recordTypeTitle);
        }

        $data[Configuration::TABLE_NAME][$newId] = [
            'type' => $event->getStatus() === 'new' ? Type::RECORDADDED : Type::RECORDUPDATED,
            'pid' => $pid,
            'table' => $table,
            'title' => sprintf('%s: %s (%s)', $recordTypeTitle, $record['title'], $event->getStatus() === 'new' ? Type::RECORDADDED : Type::RECORDUPDATED),
            'label' => $record['title'] ?? ($event->getStatus() === 'new' ? Type::RECORDADDED : Type::RECORDUPDATED) . 'with ID ' . $recordId,
            'message' => $event->getStatus() === 'new' ? Type::RECORDADDED : Type::RECORDUPDATED,
            'record' => $event->getRecordIdentifier(),
            'automatic' => 1,
            'rowDescription' => 'Automatically created by ' . basename(self::class),
        ];

        //if the record has a fe_group, set the target audience of the configuration
        $feGroupField = $GLOBALS['TCA'][$table]['ctrl']['enablecolumns']['fe_group'] ?? false;
        if ($feGroupField) {
            $feGroups = $record[$feGroupField] ?? '';

            if ($feGroups !== '') {
                $data[Configuration::TABLE_NAME][$newId]['target_audience'] = 'groups';
                $data[Configuration::TABLE_NAME][$newId]['fe_groups'] = $feGroups;
            }
        }
        /** @var BeforeConfigurationAddedEvent $dataEvent */
        $dataEvent = $eventDispatcher->dispatch(new BeforeConfigurationAddedEvent($newId, $data, $event));
        $this->rateLimitService->spamCheck($dataEvent);

        $messageQueue = GeneralUtility::makeInstance(FlashMessageService::class)
            ->getMessageQueueByIdentifier(FlashMessageQueue::NOTIFICATION_QUEUE);

        if ($dataEvent->isAddConfiguration()) {
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
            $dataHandler->start($dataEvent->getData(), []);
            $dataHandler->process_datamap();

            $messageQueue->addMessage(
                GeneralUtility::makeInstance(
                    FlashMessage::class,
                    "A notification configuration has been added for this record",
                    "Configuration added",
                    ContextualFeedbackSeverity::OK,
                    true
                )
            );

        } else {
            $messageQueue->addMessage(
                GeneralUtility::makeInstance(
                    FlashMessage::class,
                    "A notification configuration with the same specifications has been already added",
                    "Configuration not added",
                    ContextualFeedbackSeverity::INFO,
                    true
                )
            );
        }
    }
}
