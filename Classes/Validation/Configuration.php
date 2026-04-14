<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Validation;

use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Utility\AudienceUtility;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Configuration
{
    public const int SEL_USERS = 1 << 8;
    public const int SEL_GROUPS = 2 << 8;
    public const int SEL_MIXED = 3 << 8;
    public const int SEL_INVALID = 4 << 8;

    public const int SEL_MASK = self::SEL_USERS | self::SEL_GROUPS | self::SEL_MIXED | self::SEL_INVALID;

    public const int NO_USERS = 1 << 0;
    public const int NO_GROUPS = 1 << 1;
    public const int WRONG_PID = 1 << 2;
    public const int NO_RECORD_SELECTED = 1 << 3;
    public const int RECORD_DISABLED = 1 << 4;
    public const int EMPTY_AUDIENCE_WARNING = 1 << 5;
    public const int EMPTY_AUDIENCE_ERROR = 1 << 6;

    private array $record = [];

    public function __construct(protected SettingsUtility $settingsUtility)
    {
    }

    public function validate(array $record): int
    {
        $this->record = $record;
        return $this->validatePid() | $this->validateRecord() | $this->validateAudience();
    }

    private function validatePid(): int
    {
        $pid = (int)$this->record['pid'];

        if (!$this->settingsUtility->storeNotificationsOnRecordPid() && $this->settingsUtility->getNotificationStorage() !== $pid) {
            return self::WRONG_PID;
        }
        return (int)($pid === 0);
    }

    private function validateRecord(): int
    {
        $type = $this->record['type'][0] ?? 0;

        $isRecordType = in_array($type, (GeneralUtility::makeInstance(Type::class))->getTypesWithRecordField());
        $record = $this->record['record'][0] ?? null;

        if ($isRecordType && $record === null) {
            return self::NO_RECORD_SELECTED;
        }

        $disabledField = $GLOBALS['TCA'][$record['table']]['ctrl']['enablecolumns']['disabled'] ?? null;
        if ($disabledField && (bool)$record['row'][$disabledField]) {
            return self::RECORD_DISABLED;
        }

        return 0;
    }

    private function validateAudience(): int
    {
        $emptyAudienceAllowed = $this->settingsUtility->sendToEveryoneIfNoAudienceIsSelected();

        $configurationUid = (int)($this->record['l10n_parent'][0] ?? $this->record['l10n_parent'] ?? 0);
        if ($configurationUid === 0) {
            $configurationUid = (int)$this->record['uid'];
        }

        $audience = $this->record['target_audience'][0] ?? '';

        if (in_array($audience, AudienceUtility::getValidTargetAudiences(), true)) {
            if ($audience === '') {
                if ($emptyAudienceAllowed) {
                    return self::EMPTY_AUDIENCE_WARNING;
                } else {
                    return self::EMPTY_AUDIENCE_ERROR;
                }
            }

            $feUsers = $this->record['fe_users'];
            $feGroups = $this->record['fe_groups'];

            switch ($audience) {
                case 'users':
                    if (empty($feUsers)) {
                        return self::SEL_USERS | self::NO_USERS;
                    }
                    break;
                case 'groups':
                    if (empty($feGroups)) {
                        return self::SEL_GROUPS | self::NO_GROUPS;
                    }
                    break;
                case 'mixed':
                    $error = self::SEL_MIXED;
                    if (empty($feUsers)) {
                        $error |= self::NO_USERS;
                    }
                    if (empty($feGroups)) {
                        $error |= self::NO_GROUPS;
                    }
                    return $error;
            }

            return 0;
        } else {
            return self::SEL_INVALID;
        }
    }
}
