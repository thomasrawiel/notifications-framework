<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Database;

use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Events\AbstractEvent;
use TRAW\NotificationsFramework\Events\AbstractEventListener;
use TRAW\NotificationsFramework\Events\Configuration\BeforeConfigurationAddedEvent;
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
        $table = $event->getTable();
        if ($event->getStatus() === 'new' && in_array($table, $this->settingsUtility->getAllowedTables())) {
            $dataHandler = GeneralUtility::makeInstance(DataHandler::class);

            $data['tx_notifications_framework_configuration'][\TYPO3\CMS\Core\Utility\StringUtility::getUniqueId('NEW')] = [
                'type' => Type::RECORDADDED,
                'title' => 'Record added in ' . $event->getTable(),
//                'label' => 'Record added in ' . $event->getTable(),
//                'notification_text' => 'Record added in ' . $event->getTable(),
                'pid' => $event->getFieldArray()['pid'] ?? 0,
                'record' => $event->getTable() . '_' . $event->getId(),
                'rowDescription' => 'Automatically created by ' . basename(self::class),
            ];

            $eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
            $dataEvent = $eventDispatcher->dispatch(new BeforeConfigurationAddedEvent($data, $event));

            $dataHandler->start($dataEvent->getData(), []);
            $dataHandler->process_datamap();
        }

    }
}