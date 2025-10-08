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

        $table = $event->getTable();
        if ($event->getStatus() === 'new' && in_array($table, $this->settingsUtility->getAllowedTables())) {
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);

            $data[Configuration::TABLE_NAME][\TYPO3\CMS\Core\Utility\StringUtility::getUniqueId('NEW')] = [
                'type' => Type::RECORDADDED,
                'pid' => $event->getFieldArray()['pid'] ?? 0,
                'table' => $table,
                'title' => 'Record added in ' . $event->getTable(),
                'label' => BackendUtility::getRecordTitle($table, $event->getFieldArray()),
                'record' => $event->getRecordIdentifier(),
                'rowDescription' => 'Automatically created by ' . basename(self::class),
            ];

            $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
            $dataEvent = $eventDispatcher->dispatch(new BeforeConfigurationAddedEvent($data, $event));

            $dataHandler->start($dataEvent->getData(), []);
            $dataHandler->process_datamap();
        }

    }
}