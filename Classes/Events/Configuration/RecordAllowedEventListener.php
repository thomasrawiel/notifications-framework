<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Configuration;

use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\MathUtility;

#[AsEventListener(
    identifier: 'traw-notifications/record-allowed',
)]
final class RecordAllowedEventListener
{
    /**
     * Example:
     * if table pages is allowed, DON'T add a notification configuration if the added/updated record is not a default doktype
     *
     * @param RecordAllowedEvent $event
     *
     * @return void
     */
    public function __invoke(RecordAllowedEvent $event)
    {
        $table = $event->getTable();
        $fieldArray = $event->getRecordFieldArray();
        $id = $event->getRecordId();

        if ($table === 'pages') {
            $doktype = $this->resolveDoktype($table, $id, $fieldArray, $event->getDataHandler());

            $event->setRecordIsAllowed(
                in_array((int)$doktype, [1], true)
            );;
        }
    }

    private function resolveDoktype(string $table, string|int $id, array $fieldArray, DataHandler $dataHandler): ?int
    {
        if (isset($fieldArray['doktype'])) {
            return (int)$fieldArray['doktype'];
        }

        $history = $dataHandler->getHistoryRecords()[$table . ':' . $id]['newRecord']['doktype'] ?? null;
        if ($history !== null) {
            return (int)$history;
        }

        if (MathUtility::canBeInterpretedAsInteger($id)) {
            //new_id but no doktype? must be wrong
            return -1;
        }

        $record = BackendUtility::getRecord($table, $id, 'doktype');
        return isset($record['doktype']) ? (int)$record['doktype'] : null;
    }
}
