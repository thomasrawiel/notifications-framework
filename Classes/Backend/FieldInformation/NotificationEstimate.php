<?php

namespace TRAW\NotificationsFramework\Backend\FieldInformation;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Utility\AudienceUtility;
use TRAW\NotificationsFramework\Utility\FilterUtility;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class NotificationEstimate extends AbstractFormElement
{
    public const string LOOKUPFIELD = 'notification_estimate';

    private Configuration $configuration;

    private ConfigurationRepository $configurationRepository;
    private AudienceUtility $audienceUtility;

    public function __construct(?NodeFactory $nodeFactory = null, array $data = [])
    {
        parent::__construct($nodeFactory, $data);
        $this->configurationRepository = GeneralUtility::makeInstance(ConfigurationRepository::class);
        $this->audienceUtility = GeneralUtility::makeInstance(AudienceUtility::class);
    }


    public function render(): array
    {
        if ($this->data['tableName'] !== Configuration::TABLE_NAME || $this->data['fieldName'] !== self::LOOKUPFIELD) {
            throw new \RuntimeException(
                'The notification_estimate field information can only be used for the ' . self::LOOKUPFIELD . ' field of the pages table.',
                1622109821
            );
        }

        $row = $this->data['databaseRow'];
        $parameterArray = $this->data['parameterArray'];

        $fieldInformationResult = $this->renderFieldInformation();
        $fieldInformationHtml = $fieldInformationResult['html'];
        $resultArray = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);


        $estimateHtml = [];
        $noSelection = $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate.noselection');
        if ($this->data['command'] === 'new' || !MathUtility::canBeInterpretedAsInteger($row['uid'])) {
            $estimateHtml[] = '<p class="text-body-secondary">' . $noSelection . '</p>';
        } else {
            $configuration = $this->configurationRepository->findByUid($this->data['databaseRow']['uid']);
            $audience = $this->audienceUtility->getAudienceFromConfiguration($configuration);
            $totalUsers = $this->audienceUtility->getUsersCountFromConfiguration($configuration);

            if (!empty($audience)) {
                $groups = count($audience['groups'] ?? []);
                $users = count($audience['users'] ?? []);
                $estimateHtml[] = '<p class="text-body-secondary">';
                $estimateHtml[] = '<ul>';
                $groupsSelected = $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate.count.groups');
                $estimateHtml[] = "<li>$groups $groupsSelected</li>";
                $usersSelected = $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate.count.users');
                $estimateHtml[] = "<li>$users $usersSelected</li>";
                $estimateHtml[] = '</ul>';
            } else {
                $estimateHtml[] = '<p class="text-body-secondary">' . $noSelection . '</p>';
            }

            if ($totalUsers > 0) {
                $LLLtotalUsers = $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate.totalusers');
                $estimateHtml[] = '<p class="text-body-secondary"><strong>' . $totalUsers . '</strong> ' . $LLLtotalUsers . '</p>';
            }
        }

        $html = [];
        $html[] = '<div class="formengine-field-item t3js-formengine-field-item">';
        $html[] = $fieldInformationHtml;
        $html[] = '<div class="form-wizards-wrap">';
        $html[] = '<div class="form-wizards-element">';
        $html[] = '<div class="form-control-wrap">';
        $html[] = implode(LF, $estimateHtml);
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $html[] = '</div>';
        $resultArray['html'] = implode(LF, $html);

        return $resultArray;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
