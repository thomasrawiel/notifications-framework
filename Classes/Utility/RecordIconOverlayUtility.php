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

        $configurationRecord = BackendUtility::getRecord(Configuration::TABLE_NAME, $row['sys_language_uid'] > 0 ? ($row['l10n_parent'][0] ?? $row['l10n_parent']) : $row['uid']);
        if ($configurationRecord['done'] ?? false) {
            return self::ICON_IDENTIFIER_CHECK;
        }

        if ($configurationRecord['push'] ?? false) {
            return self::ICON_IDENTIFIER_QUEUE;
        }

        if (empty($row['target_audience'])) {
            return self::ICON_IDENTIFIER_QUESTION;
        }

        return self::ICON_IDENTIFIER_PAUSE;
    }
}
