<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Backend\FieldInformation;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Utility\AudienceUtility;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
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
        $resultArray = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);

        if(str_starts_with((string)$this->data['databaseRow']['uid'], 'NEW')) {
            $resultArray['html'] = $this->callout('Validation pending', 'Please save the record first.');
            return $resultArray;
        }

        $resultArray['html'] = implode(LF, array_filter(
            [
                $this->validatePid(),
                $this->validateAudience(),
            ]
        ));

        return $resultArray;
    }

    private function validateAudience(): string
    {
        $audience = $this->data['databaseRow']['target_audience'][0] ?? null;
        $emptyAudienceAllowed = $this->settingsUtility->sendToEveryoneIfNoAudienceIsSelected();

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
                        return $this->error('Mixed audience', 'No groups selected, but the configuration allows it.');
                    }
                    if (empty($feUsers) && !empty($feGroups)) {
                        return $this->warning('Missing users', 'Mixed audience is selected, but no users are selected. Switch to groups?');
                    }
                    if (!empty($feUsers) && empty($feGroups)) {
                        return $this->warning('Mixed audience', 'Mixed audience is selected, but no groups are selected. Select users instead? <a href="">Yes, set value</a>');
                    }
                    break;
            }
        } else {
            return $this->error('Invalid audience', 'The audience "' . $audience . '" is not vali.');
        }

        return '';
    }

    private function validatePid(): string
    {
        if ($this->settingsUtility->storeNotificationsOnRecordPid()) {

        } else {
            $pid = $this->data['databaseRow']['pid'];
            if ($this->settingsUtility->getNotificationStorage() !== $pid) {
                return $this->error('Wrong storage', 'You have configued a specific notification storage. Move this configuration to page <strong>' . $this->settingsUtility->getNotificationStorage() . '</strong>');
            }
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
