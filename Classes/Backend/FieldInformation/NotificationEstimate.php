<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Backend\FieldInformation;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Utility\AudienceUtility;
use TRAW\NotificationsFramework\Utility\FilterUtility;
use TYPO3\CMS\Backend\Form\AbstractNode;
use TYPO3\CMS\Backend\Form\Element\AbstractFormElement;
use TYPO3\CMS\Backend\Form\NodeFactory;
use TYPO3\CMS\Core\Imaging\Icon;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class NotificationEstimate extends AbstractCustomNode
{
    public const string LOOKUPFIELD = 'notification_estimate';

    public static array $requiredTca = [
        'exclude' => true,
        'label' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate',
        'description' => 'LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate.description',
        'config' => [
            'type' => 'none',
            'renderType' => 'notificationEstimate',
            'fieldInformation' => [
                'tcaDescription' => [
                    'renderType' => 'tcaDescription',
                ],
            ],
        ],
    ];

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

        $fieldInformationResult = $this->renderFieldInformation();
        $result = $this->mergeChildReturnIntoExistingResult($this->initializeResultArray(), $fieldInformationResult, false);


        $estimateHtml = [];
        $noSelection = $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate.noselection');
        if ($this->data['command'] === 'new' || !MathUtility::canBeInterpretedAsInteger($row['uid']) || $row['hidden'] || $row['deleted'] === 1) {
            $estimateHtml[] = '<p class="text-body-secondary">' . $noSelection . '</p>';
            if($row['hidden']) {
                $estimateHtml[] = $this->info('Record hidden', 'No audience was calculated because the record is hidden', '<a href="#" class="btn btn-default js-notification-configuration-ajax" data-field="hidden" data-value="0" data-uid="' . $row['uid'] . '" data-table="tx_notifications_framework_domain_model_configuration">' . $this->iconFactory->getIcon('actions-lightbulb-on', Icon::SIZE_MEDIUM)->render() . 'Enable record</a>');
            }
        } else {
            /** @var Configuration $configuration */
            $configuration = $this->configurationRepository->findByUid($row['uid']);

            $audience = $this->audienceUtility->getAudienceFromConfiguration($configuration);
            $totalUsers = $this->audienceUtility->getUsersCountFromConfiguration($configuration);


            $estimateHtml[] = '<p class="text-body-secondary">';
            $estimateHtml[] = '<ul>';
            $targetAudience = $configuration->getTargetAudience();
            $targetAudienceLabel = $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:configuration.target_audience');
            $targetAudienceValue = $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:configuration.target_audience.' . $targetAudience);
            if (empty($targetAudienceValue)) {
                $targetAudienceValue = $targetAudience;
            }
            $estimateHtml[] = "<li>$targetAudienceLabel: $targetAudienceValue</li>";
            if (!empty($audience)) {
                $groups = count($audience['groups'] ?? []);
                $users = count($audience['users'] ?? []);

                if (in_array($targetAudience, ['users', 'mixed'], true)) {
                    $usersSelected = $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate.count.users');
                    $estimateHtml[] = "<li>$users $usersSelected</li>";
                }
                if (in_array($targetAudience, ['groups', 'mixed'], true)) {
                    $groupsSelected = $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate.count.groups');
                    $estimateHtml[] = "<li>$groups $groupsSelected</li>";
                }

            }
            $estimateHtml[] = '</ul>';

            if (empty($audience)) {
                $estimateHtml[] = '<p class="text-body-secondary">' . $noSelection . '</p>';
            }

            if ($totalUsers > 0) {
                $LLLtotalUsers = $this->getLanguageService()->sL('LLL:EXT:notifications_framework/Resources/Private/Language/locallang_tca.xlf:notification_estimate.totalusers');
                $estimateHtml[] = $this->info('Notifcation estimate', '<strong>' . $totalUsers . '</strong> ' . nl2br($LLLtotalUsers));
            }
        }

        $result['html'] = $this->renderHtml($fieldInformationResult['html'], $estimateHtml);

        return $result;
    }

    protected function getLanguageService(): LanguageService
    {
        return $GLOBALS['LANG'];
    }
}
