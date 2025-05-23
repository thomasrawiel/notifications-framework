<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TYPO3\CMS\Backend\Utility\BackendUtility;

final class RecordIconOverlayUtility
{
    public const ICON_IDENTIFIER_CHECK = 'tx-nf-overlay-check';
    public const ICON_IDENTIFIER_QUESTION = 'tx-nf-overlay-question';
    public const ICON_IDENTIFIER_PAUSE = 'tx-nf-overlay-pause';
    public const ICON_IDENTIFIER_QUEUE = 'tx-nf-overlay-queue';

    public static function getRecordIconOverlay($row): string
    {
        if ($row['hidden']) {
            return '';
        }

        if (empty($row['target_audience'])) {
            return self::ICON_IDENTIFIER_QUESTION;
        }

        $configurationRecord = BackendUtility::getRecord(Configuration::TABLE_NAME, $row['uid']);
        if ($configurationRecord['done']) {
            return self::ICON_IDENTIFIER_CHECK;
        }

        return $configurationRecord['push']
            ? self::ICON_IDENTIFIER_QUEUE
            : self::ICON_IDENTIFIER_PAUSE;

    }
}