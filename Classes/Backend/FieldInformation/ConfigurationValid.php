<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Backend\FieldInformation;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Utility\AudienceUtility;
use TRAW\NotificationsFramework\Utility\RecordUtility;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationValid extends AbstractFormElement
{
    private SettingsUtility $settingsUtility;

    public static array $requiredTca = [
        'exclude' => true,
        'label' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:configuration_pid_valid',
        'description' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:configuration_pid_valid.description',
        'config' => [
            'type' => 'none',
            'renderType' => 'configurationValid',
            'fieldInformation' => [
                'tcaDescription' => [
                    'renderType' => 'tcaDescription',
                ],
            ],
        ],
    ];

    public function __construct(?NodeFactory $nodeFactory = null, array $data = [])
    {
        parent::__construct($nodeFactory, $data);
        $this->settingsUtility = GeneralUtility::makeInstance(SettingsUtility::class);
    }

    public function render(): array
    {
        if ($this->data['tableName'] !== Configuration::TABLE_NAME) {
            throw new \RuntimeException(
                'The configurationPidValid field information can only be used for the ' . Configuration::TABLE_NAME . ' table.',
                1622109821
            );
        }

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $result = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);

        if (str_starts_with((string)$this->data['databaseRow']['uid'], 'NEW')) {
            $result['html'] = $this->callout('Validation pending', 'Please save the record first.');
            return $result;
        }

        $result['html'] = implode(LF, array_filter(
            [
                $this->validatePid(),
                $this->validateAudience(),
                $this->validateRecord(),
            ]
        ));

        $result['javaScriptModules'][] = JavaScriptModuleInstruction::create(
            '@traw/notifications-framework/Configuration.js',
        );

        return $result;
    }

    private function validateAudience(): string
    {
        if ($this->data['vanillaUid'] <= 0) {
            return '';
        }

        $audience = $this->data['databaseRow']['target_audience'][0] ?? null;
        $emptyAudienceAllowed = $this->settingsUtility->sendToEveryoneIfNoAudienceIsSelected();

        $configurationUid = (int)($this->data['databaseRow']['l10n_parent'][0] ?? $this->data['databaseRow']['l10n_parent'] ?? 0);
        if ($configurationUid === 0) {
            $configurationUid = (int)$this->data['databaseRow']['uid'];
        }

        if (in_array($audience, AudienceUtility::getValidTargetAudiences())) {
            if ($audience === '') {
                if ($emptyAudienceAllowed) {
                    return $this->warning('Empty audience', 'No audience is selected, but the configuration allows it, so that is okay, if you know what you\'re doing.');
                } else {
                    return $this->error('empty not allowed');
                }
            }

            $feUsers = $this->data['databaseRow']['fe_users'];
            $feGroups = $this->data['databaseRow']['fe_groups'];

            switch ($audience) {
                case 'users':
                    if (empty($feUsers)) {
                        return $this->error('User audience', 'No users selected, but the configuration allows it.');
                    }
                    break;
                case 'groups':
                    if (empty($feGroups)) {
                        return $this->error('Group audience', 'No groups selected, but the configuration allows it.');
                    }
                    break;
                case 'mixed':
                    if (empty($feUsers) && empty($feGroups)) {
                        return $this->error('Mixed audience', 'Mixed audience is selected, but no users or groups selected');
                    }
                    if (empty($feUsers) && !empty($feGroups)) {
                        return $this->warning('Missing users', 'Mixed audience is selected, but no users are selected. <a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="target_audience" data-value="groups" data-uid="' . $configurationUid . '">Change audience to groups</a>');
                    }
                    if (!empty($feUsers) && empty($feGroups)) {
                        return $this->warning('Mixed groups', 'Mixed audience is selected, but no groups are selected. <a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="target_audience" data-value="users"  data-uid="' . $configurationUid . '">Change audience to users</a>');
                    }
                    break;
            }
        } else {
            return $this->error('Invalid audience', 'The audience "' . $audience . '" is not valid.');
        }

        return '';
    }

    private function validatePid(): string
    {
        if (!$this->settingsUtility->storeNotificationsOnRecordPid()) {
            $pid = $this->data['databaseRow']['pid'];
            if ($this->settingsUtility->getNotificationStorage() !== $pid) {
                return $this->error('Wrong storage', 'You have configued a specific notification storage. Move this configuration to page <strong>' . $this->settingsUtility->getNotificationStorage() . '</strong>');
            }
        }

        return '';
    }

    private function validateRecord(): string
    {
        $type = $this->data['databaseRow']['type'][0] ?? null;
        $record = $this->data['databaseRow']['record'][0] ?? null;
        $isRecordType = in_array($type, (GeneralUtility::makeInstance(Type::class))->getTypesWithRecordField());

        if($isRecordType && $record === null) {
            return $this->error('No record', 'No record selected');
        }

        $disabledField = $GLOBALS['TCA'][$record['table']]['ctrl']['enablecolumns']['disabled'] ?? null;
        if($disabledField && (bool)$record['row'][$disabledField]) {
            return $this->warning('Record disabled', 'There is a record selected, but it is disabled. <a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="'.$disabledField.'" data-value="0" data-uid="' . $record['uid'] . '" data-table="'.$record['table'].'">Enable record</a>');
        }

        return '';
    }

    private function callout(string $title = '', string $body = '', string $type = ''): string
    {
        $pattern = '<div class="t3js-infobox callout callout-sm callout-%s"><div class="callout-title">%s</div><div class="callout-body">%s</div></div>';
        return sprintf($pattern, $type, $title, $body);
    }

    private function success(string $title = '', string $body = ''): string
    {
        return $this->callout($title, $body, 'success');
    }

    private function warning(string $title = '', string $body = ''): string
    {
        return $this->callout($title, $body, 'warning');
    }

    private function error(string $title = '', string $body = ''): string
    {
        return $this->callout($title, $body, 'danger');
    }
}
