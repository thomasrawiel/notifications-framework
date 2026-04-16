<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Validation;

use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Utility\AudienceUtility;
use TRAW\NotificationsFramework\Utility\RecordUtility;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationValidation
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
        $this->record = $this->convertRecordToValidatableArray($record);
        return $this->executeValidation();
    }

    public function validateConfiguration(Configuration $configuration): bool
    {
        $this->record = $this->convertRecordToValidatableArray($configuration);
        $valid = $this->executeValidation();

        return $valid === 0;
    }

    private function convertRecordToValidatableArray(array|Configuration $record): array
    {
        if (is_array($record)) {
            return [
                'uid' => (int)$record['uid'],
                'l10n_parent' => (int)($record['l10n_parent'][0] ?? $record['l10n_parent'] ?? 0),
                'pid' => (int)$record['pid'],
                'record' => $record['record'][0] ?? null,
                'table' => $record['table'],
                'target_audience' => $record['target_audience'][0] ?? '',
                'fe_users' => $record['fe_users'] ?? '',
                'fe_groups' => $record['fe_groups'] ?? '',
            ];
        }

        if ($record instanceof Configuration) {
            $table = RecordUtility::getTableFromRecordString($record->getRecord());
            $recordUid = RecordUtility::getRecordUidAsIntegerFromRecordString($record->getRecord());
            $attachedRecord = BackendUtility::getRecord($table, $recordUid);

            return [
                'uid' => $record->getUid(),
                'l10n_parent' => $record->getL10nParent(),
                'pid' => $record->getPid(),
                'record' => [
                    'uid' => $attachedRecord['uid'],
                    'pid' => $attachedRecord['pid'],
                    'table' => $table,
                    'row' => $attachedRecord,
                ],
                'table' => $table,
                'target_audience' => $record->getTargetAudience(),
                'fe_users' => $record->getFeUsers(),
                'fe_groups' => $record->getFeGroups(),
            ];
        }

        return [];
    }


    private function executeValidation(): int
    {
        return $this->validatePid() | $this->validateRecord() | $this->validateAudience();
    }

    private function validatePid(): int
    {
        $pid = (int)$this->record['pid'];

        return $this->settingsUtility->isPidValid($pid) === false
            ? self::WRONG_PID
            : 0;
    }

    private function validateRecord(): int
    {
        $type = $this->record['type'][0] ?? 0;

        $isRecordType = in_array($type, (GeneralUtility::makeInstance(Type::class))->getTypesWithRecordField());
        $record = $this->record['record'] ?? null;

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

        $configurationUid = (int)($this->record['l10n_parent']);
        if ($configurationUid === 0) {
            $configurationUid = $this->record['uid'];
        }

        $audience = $this->record['target_audience'];

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
