<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use TRAW\NotificationsFramework\Validation\ConfigurationValidation;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final readonly class ValidationUtility
{
    private IconFactory $iconFactory;

    public function __construct(private readonly SettingsUtility $settingsUtility)
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
    }

    public function getAction(int $valid, array $configuration = [], $linkAction = true, string|bool $priority = false, $iconSize = Icon::SIZE_MEDIUM): string
    {
        $result = ConfigurationValidation::getInterpretation($valid, $priority);
        $selection = $valid & ConfigurationValidation::SEL_MASK;

        switch ($result) {
            case ConfigurationValidation::NO_GROUPS:
                $data = [
                    'uid' => $configuration['uid'],
                    'field' => 'target_audience',
                    'table' => 'tx_notifications_framework_configuration',
                    'value' => 'users',
                    'action' => 'change_audience_users',
                    'icon' => $selection === ConfigurationValidation::SEL_MIXED ? 'apps-pagetree-page-frontend-users' : '',
                ];
                break;
            case ConfigurationValidation::NO_USERS:
                $data = [
                    'uid' => $configuration['uid'],
                    'field' => 'target_audience',
                    'table' => 'tx_notifications_framework_configuration',
                    'value' => 'groups',
                    'action' => 'change_audience_groups',
                    'icon' => $selection === ConfigurationValidation::SEL_MIXED ? 'apps-pagetree-folder-contains-fe_users' : '',
                ];
                break;
            case ConfigurationValidation::RECORD_DISABLED:
                $record = is_array($configuration['record']) ? ($configuration['record'][0] ?? $configuration['record']) : $configuration['record'];
                //$disabledField = $GLOBALS['TCA'][$record['table']]['ctrl']['enablecolumns']['disabled'] ?? null;
                if (is_array($record)) {
                    $disabledField = $GLOBALS['TCA'][$record['table']]['ctrl']['enablecolumns']['disabled'];
                    if ((bool)$record['row'][$disabledField]) {
                        $data = [
                            'uid' => $record['uid'],
                            'field' => $disabledField,
                            'table' => $record['table'],
                            'value' => 0,
                            'action' => 'enable_record',
                            'icon' => 'actions-lightbulb-on',
                        ];
                    }
                    $disabledField = $GLOBALS['TCA']['tx_notifications_framework_configuration']['ctrl']['enablecolumns']['disabled'];
                    if ((bool)$configuration[$disabledField]) {
                        $data = [
                            'uid' => $configuration['uid'],
                            'field' => $disabledField,
                            'table' => 'tx_notifications_framework_configuration',
                            'value' => 0,
                            'action' => 'enable_configuration',
                            'icon' => 'actions-lightbulb-on',
                        ];
                    }
                }


                break;
            case ConfigurationValidation::WRONG_PID:
                $data = [
                    'icon' => 'apps-pagetree-drag-move-into',
                ];
                break;
            case ConfigurationValidation::EMPTY_AUDIENCE_WARNING:
                $overrideAction = 'valid32';
                $data = [];
                $data['icon2'] = 'actions-question';
            case 0:
                $data ??= [];
                $data['icon'] = 'actions-check';
                $data['action'] = $overrideAction ?? 'valid';

                if (!($configuration['push'] ?? false)) {
                    $data = [
                        'uid' => $configuration['uid'],
                        'field' => 'push',
                        'value' => 1,
                        'table' => 'tx_notifications_framework_configuration',
                        'action' => 'queue',
                        'icon' => 'actions-cloud-upload',
                    ];
                }
                break;
        }

        return $this->getActionMarkup($data ?? [], $linkAction, $iconSize);
    }

    public function getNotificationLevel(int $valid, array $configuration = []): string
    {
        $result = ConfigurationValidation::getInterpretation($valid);
        $selection = $valid & ConfigurationValidation::SEL_MASK;

        switch ($result) {
            case ConfigurationValidation::NO_GROUPS:
                return $selection === ConfigurationValidation::SEL_MIXED ? 'warning' : 'danger';
            case ConfigurationValidation::NO_USERS:
                return $selection === ConfigurationValidation::SEL_MIXED ? 'warning' : 'danger';
            case ConfigurationValidation::RECORD_DISABLED:
                return 'warning';
            case ConfigurationValidation::EMPTY_AUDIENCE_WARNING:
                return 'warning';
            case 0:
                return 'success';

        }

        //return '';
    }

    private function getIconMarkup(string $iconIdentifier, string $iconSize, string $title = ''): string
    {
        if ($iconIdentifier === '') {
            return '';
        }

        $icon = $this->iconFactory->getIcon($iconIdentifier, $iconSize);
        if ($title) {
            $icon->setTitle($title);
        }
        return $icon->render();
    }

    private function getActionMarkup(array $data, bool $linkAction, string $iconSize): string
    {
        $actionLabel = $this->translate('action.' . ($data['action'] ?? ''));
        $iconMarkup = $this->getIconMarkup($data['icon'] ?? '', $iconSize, $actionLabel);
        if ($data['icon2'] ?? false) {
            $iconMarkup .= $this->getIconMarkup($data['icon2'] ?? '', $iconSize, $actionLabel);
        }

        if (!isset($data['uid']) || !isset($data['field']) || !isset($data['value']) || $linkAction === false) {
            return $iconMarkup;
        }

        $pattern = '<a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="%s" data-value="%s" data-uid="%s" data-table="%s">%s%s</a>';
        return sprintf($pattern, $data['field'], $data['value'], $data['uid'], $data['table'] ?? null, $iconMarkup, $actionLabel);
    }

    private function translate(string $input): string
    {
        return $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_backend.xlf:' . $input);
    }

    private function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
