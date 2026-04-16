<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Backend\FieldInformation;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Utility\AudienceUtility;
use TRAW\NotificationsFramework\Utility\RecordUtility;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TRAW\NotificationsFramework\Validation\ConfigurationValidation;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ConfigurationValid extends AbstractFormElement
{
    private SettingsUtility $settingsUtility;
    private ConfigurationValidation $configurationValidation;

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
        $this->configurationValidation = GeneralUtility::makeInstance(ConfigurationValidation::class);
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

        $valid = $this->configurationValidation->validate($this->data['databaseRow']);

        $result['html'] = implode(LF, array_filter(
            [
                $valid > 0 ? $this->info('Configuration incomplete', 'Some options are not valid or will lead to the configuration being skipped') : $this->success('Configuration complete', 'This configuration can be processed.', $this->data['databaseRow']['push'] ? '' : '<a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="push" data-value="1" data-uid="' . $configurationUid . '">Queue for processing</a>'),
                $this->validatePid($valid),
                $this->validateAudience($valid),
                $this->validateRecord($valid),
            ]
        ));

        $result['javaScriptModules'][] = JavaScriptModuleInstruction::create(
            '@traw/notifications-framework/Configuration.js',
        );

        return $result;
    }

    private function validateAudience(int $valid): string
    {
        if ($this->data['vanillaUid'] <= 0) {
            return '';
        }

        if ($valid === 0) {
            return '';
        }
        $selection = $valid & ConfigurationValidation::SEL_MASK;

        if ($valid & ConfigurationValidation::EMPTY_AUDIENCE_WARNING) {
            return $this->warning('Empty audience', 'No audience is selected, but the configuration allows it, so that is okay, if you know what you\'re doing.');
        }

        if ($valid & ConfigurationValidation::EMPTY_AUDIENCE_ERROR) {
            return $this->error('empty audience is not allowed');
        }

        if ($selection === ConfigurationValidation::SEL_INVALID) {
            return $this->error('Invalid audience', 'The selected audience is not valid.');
        }

        if ($selection === ConfigurationValidation::SEL_USERS && ($valid & ConfigurationValidation::NO_USERS)) {
            return $this->error('User audience', 'No users selected');
        }

        if ($selection === ConfigurationValidation::SEL_GROUPS && ($valid & ConfigurationValidation::NO_GROUPS)) {
            return $this->error('Group audience', 'No groups selected');
        }

        if (
            $selection === ConfigurationValidation::SEL_MIXED
            && ($valid & ConfigurationValidation::NO_USERS)
            && ($valid & ConfigurationValidation::NO_GROUPS)
        ) {
            return $this->error('Mixed audience', 'Mixed audience is selected, but no users or groups selected');
        }

        $configurationUid = (int)($this->data['databaseRow']['l10n_parent'][0] ?? $this->data['databaseRow']['l10n_parent'] ?? 0);
        if ($configurationUid === 0) {
            $configurationUid = (int)$this->data['databaseRow']['uid'];
        }
        if ($selection === ConfigurationValidation::SEL_MIXED && ($valid & ConfigurationValidation::NO_USERS)) {
            return $this->warning('Missing users', 'Mixed audience is selected, but no users are selected.', '<a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="target_audience" data-value="groups" data-uid="' . $configurationUid . '">Change audience to groups</a>');
        }

        if ($selection === ConfigurationValidation::SEL_MIXED && ($valid & ConfigurationValidation::NO_GROUPS)) {
            return $this->warning('Missing groups', 'Mixed audience is selected, but no groups are selected.', '<a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="target_audience" data-value="users"  data-uid="' . $configurationUid . '">Change audience to users</a>');
        }

        return '';
    }

    private function validatePid(int $valid): string
    {
        $configurationUid = (int)($this->data['databaseRow']['l10n_parent'][0] ?? $this->data['databaseRow']['l10n_parent'] ?? 0);
        if ($configurationUid === 0) {
            $configurationUid = (int)$this->data['databaseRow']['uid'];
        }

        return ($valid & ConfigurationValidation::WRONG_PID)
            ? $this->error('Wrong storage', 'You have configued a specific notification storage.', '<a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="pid" data-value="' . $this->settingsUtility->getNotificationStorage() . '"  data-uid="' . $configurationUid . '">Move this configuration to page <strong>' . $this->settingsUtility->getNotificationStorage() . '</strong></a>')
            : '';
    }

    private function validateRecord(int $valid): string
    {
        if ($valid & ConfigurationValidation::NO_RECORD_SELECTED) {
            return $this->error('No record', 'No record selected');
        }
        $disabledField = $GLOBALS['TCA'][$this->data['databaseRow']['table']]['ctrl']['enablecolumns']['disabled'] ?? null;
        if ($valid & ConfigurationValidation::RECORD_DISABLED) {
            return $this->warning('Record disabled', 'There is a record selected, but it is disabled.', '<a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="' . $disabledField . '" data-value="0" data-uid="' . $this->data['databaseRow']['record'][0]['uid'] . '" data-table="' . $this->data['databaseRow']['record'][0]['table'] . '">Enable record</a>');
        }

        return '';
    }

    private function callout(string $title = '', string $body = '', string $action = '', string $type = ''): string
    {
        $pattern = '<div class="t3js-infobox callout callout-sm callout-%s"><div class="media"><div class="media-left"><span class="icon-emphasized">%s</span></div><div class="media-body"><div class="callout-title"><strong>%s</strong></div><div class="callout-body"><p class="mt-4">%s</p>%s</div></div></div></div>';
        $icon = $this->getCalloutIcon($type, $action);
        return sprintf($pattern, $type, $icon, $title, $body, $action);
    }

    private function getCalloutIcon(string $type = '', string $action = ''): string
    {
        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $icon = $iconFactory->getIcon('actions-info', Icon::SIZE_SMALL);

        $iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $icon = $iconFactory->getIcon('actions-info', Icon::SIZE_SMALL);

        if ($type === 'danger') {
            $icon = $iconFactory->getIcon('actions-exclamation', Icon::SIZE_SMALL);
        }

        if ($type === 'success') {
            $icon = $iconFactory->getIcon('actions-check', Icon::SIZE_SMALL);
        }

        if ($action !== '') {
            $icon = $iconFactory->getIcon('actions-question', Icon::SIZE_SMALL);
        }

        return $icon->render();
    }

    private function info(string $title = '', string $body = '', string $action = ''): string
    {
        return $this->callout($title, $body, $action, 'info');
    }

    private function success(string $title = '', string $body = '', string $action = ''): string
    {
        return $this->callout($title, $body, $action, 'success');
    }

    private function warning(string $title = '', string $body = '', string $action = ''): string
    {
        return $this->callout($title, $body, $action, 'warning');
    }

    private function error(string $title = '', string $body = '', string $action = ''): string
    {
        return $this->callout($title, $body, $action, 'danger');
    }
}
