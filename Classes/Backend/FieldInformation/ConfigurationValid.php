<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Backend\FieldInformation;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Utility\AudienceUtility;
use TRAW\NotificationsFramework\Utility\RecordUtility;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TRAW\NotificationsFramework\Utility\ValidationUtility;
use TRAW\NotificationsFramework\Validation\ConfigurationValidation;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Imaging\IconRegistry;
use TYPO3\CMS\Core\Page\JavaScriptModuleInstruction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class ConfigurationValid extends AbstractCustomNode
{
    private SettingsUtility $settingsUtility;
    private ConfigurationValidation $configurationValidation;
    private ValidationUtility $validationUtility;


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
        $this->validationUtility = GeneralUtility::makeInstance(ValidationUtility::class);
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
        $result = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);

        $validationHtml = [];
        if (str_starts_with((string)$this->data['databaseRow']['uid'], 'NEW')) {
            $validationHtml[] = $this->info('Validation pending', 'Please save the record first.');
            return $result;
        }

        $configurationUid = (int)($this->data['databaseRow']['l10n_parent'][0] ?? $this->data['databaseRow']['l10n_parent'] ?? 0);
        if ($configurationUid === 0) {
            $configurationUid = (int)$this->data['databaseRow']['uid'];
        }
        $valid = $this->configurationValidation->validate($this->data['databaseRow']);


        if ($valid !== 0 && $valid !== ConfigurationValidation::EMPTY_AUDIENCE_WARNING) {
            $validationHtml[] = $this->info('Configuration incomplete', 'Some options are not valid or ambigious and will lead to the configuration being skipped.');
        } else {
            $validationHtml[] = $this->success('Configuration complete', 'This configuration can be processed.', $this->validationUtility->getAction($valid, $this->data['databaseRow']));
        }

        $validationHtml[] = $this->getValidationTextPid($valid);
        $validationHtml[] = $this->getValidationTextRecord($valid);
        $validationHtml[] = $this->getValidationTextAudience($valid);

        $result['javaScriptModules'][] = JavaScriptModuleInstruction::create(
            '@traw/notifications-framework/Configuration.js',
        );
        $result['html'] = $this->renderHtml($fieldInformationResult['html'], array_filter($validationHtml));
        return $result;
    }

    private function getValidationTextAudience(int $valid): string
    {
        if ($this->data['vanillaUid'] <= 0) {
            return '';
        }

        if ($valid === 0) {
            return '';
        }

        //$interpretation = ConfigurationValidation::getInterpretation($valid, 'audience');

        if ($valid & ConfigurationValidation::EMPTY_AUDIENCE_WARNING) {
            return $this->warning('Empty audience', 'No audience is selected, but the configuration allows it, so that is okay, if you know what you\'re doing.');
        }

        if ($valid & ConfigurationValidation::EMPTY_AUDIENCE_ERROR) {
            return $this->error('empty audience is not allowed');
        }

        $selection = $valid & ConfigurationValidation::SEL_MASK;
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
            return $this->warning('Missing users', 'Mixed audience is selected, but no users are selected.', $this->validationUtility->getAction($valid, $this->data['databaseRow']));
        }

        if ($selection === ConfigurationValidation::SEL_MIXED && ($valid & ConfigurationValidation::NO_GROUPS)) {
            return $this->warning('Missing groups', 'Mixed audience is selected, but no groups are selected.', $this->validationUtility->getAction($valid, $this->data['databaseRow']));
        }

        return '';
    }

    private function getValidationTextPid(int $valid): string
    {
        $configurationUid = (int)($this->data['databaseRow']['l10n_parent'][0] ?? $this->data['databaseRow']['l10n_parent'] ?? 0);
        if ($configurationUid === 0) {
            $configurationUid = (int)$this->data['databaseRow']['uid'];
        }

        $validation = ConfigurationValidation::getInterpretation($valid, 'record');

        return ($validation === ConfigurationValidation::WRONG_PID)
            ? $this->error('Wrong storage', 'You have configued a specific notification storage.', '<a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="pid" data-value="' . $this->settingsUtility->getNotificationStorage()[0] . '"  data-uid="' . $configurationUid . '">' . $this->iconFactory->getIcon('apps-pagetree-drag-move-into', Icon::SIZE_MEDIUM)->render() . ' Move this configuration to page <strong>' . $this->settingsUtility->getNotificationStorage()[0] . '</strong></a>')
            : '';
    }

    private function getValidationTextRecord(int $valid): string
    {
        $validation = ConfigurationValidation::getInterpretation($valid, 'record');

        if ($validation === ConfigurationValidation::NO_RECORD_SELECTED) {
            return $this->error('No record', 'No record selected');
        }
        if ($validation === ConfigurationValidation::RECORD_DISABLED_SELF) {
            return $this->error('Configuration disabled', 'This configuration is disabled.', $this->validationUtility->getAction($valid, $this->data['databaseRow'], true, 'record'));
        }
        if ($validation === ConfigurationValidation::RECORD_DISABLED_ATTACHED) {
            return $this->error('Record disabled', 'The selected record is disabled.', $this->validationUtility->getAction($valid, $this->data['databaseRow'], true, 'record'));
        }

        return '';
    }
}
