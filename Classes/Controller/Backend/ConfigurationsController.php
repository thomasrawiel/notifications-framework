<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Utility\AudienceUtility;
use TRAW\NotificationsFramework\Utility\RecordUtility;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TRAW\NotificationsFramework\Utility\TreeListUtility;
use TRAW\NotificationsFramework\Validation\ConfigurationValidation;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Module\ModuleData;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;

#[AsController]
class ConfigurationsController extends AbstractController
{
    public function __construct(
        protected readonly ModuleTemplateFactory   $moduleTemplateFactory,
        protected readonly UriBuilder              $uriBuilder,
        protected readonly ConfigurationRepository $configurationRepository,
        protected readonly SettingsUtility         $settingsUtility,
        protected readonly TreeListUtility         $treeListUtility,
        private readonly AudienceUtility           $audienceUtility,
        private readonly ConfigurationValidation   $configurationValidation,
    )
    {
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->initializeModuleTemplate($request);

        /** @var ModuleData $moduleData */
        $moduleData = $request->getAttribute('moduleData');

        $demand = [
            'sortField' => $moduleData->get('sortField'),
            'sortDirection' => in_array($moduleData->get('sortDirection'), ['asc', 'desc']) ? $moduleData->get('sortDirection') : 'asc',
            'filter' => is_array($moduleData->get('filter')) ? $moduleData->get('filter') : ['type' => '', 'valid' => '', 'status' => 'all'],
            'uid' => null,
            'pid' => $this->settingsUtility->storeNotificationsOnRecordPid() ? $this->selectedPageUID : $this->treeListUtility->getTreeListArrayFromArray($this->settingsUtility->getNotificationStorage(), $this->settingsUtility->getNotificationStorageRecursive()),
            'currentPage' => (int)($moduleData->get('currentPage') > 0 ? $moduleData->get('currentPage') : 1),
            'perPage' => (int)($moduleData->get('perPage') > 0 ? $moduleData->get('perPage') : 10),
        ];

        $configurations = array_map(function ($configuration) {
            $configuration['valid'] = $this->configurationValidation->validate($configuration);
            if (!$configuration['hidden']) {
                $configuration['audience'] = $this->audienceUtility->getUsersCountFromConfiguration($this->configurationRepository->findByUid($configuration['uid']));
            } else {
                $configuration['audience'] = 0;
            }
            $recordString = $configuration['record'];
            if (!empty($recordString)) {
                $table = RecordUtility::getTableFromRecordString($recordString);
                $recordUid = RecordUtility::getRecordUidAsIntegerFromRecordString($recordString);
                $attachedRecord = BackendUtility::getRecord($table, $recordUid);
                $configuration['record'] = [
                    'uid' => $attachedRecord['uid'],
                    'pid' => $attachedRecord['pid'],
                    'table' => $table,
                    'row' => $attachedRecord,
                ];
            }

            $configuration['status'] = 'ready';
            if ($configuration['push']) {
                $configuration['status'] = 'queue';
            }
            if ($configuration['done']) {
                $configuration['status'] = 'done';
            }

            return $configuration;
        }, $this->configurationRepository->getConfigurationsByDemand($demand));

        $configurations = $this->applyFilters($configurations, $demand['filter'] ?? []);
        $configurations = $this->sortList($configurations, $demand['sortField'], $demand['sortDirection']);

        $this->moduleTemplate->assignMultiple($this->buildPagination($configurations, $demand['currentPage'], $demand['perPage']));
        $this->moduleTemplate->assignMultiple([
            'demand' => $demand,
            'action' => 'listConfigurations',
            'filters' => [
                'type' => array_values(array_unique(
                    array_column($configurations, 'type')
                )),
                'valid' => array_values(array_unique(
                    array_column($configurations, 'valid')
                )),
                'status' => ['all', 'ready', 'queue', 'done'],

            ],
            'currentPage' => (int)$moduleData->get('currentPage'),
        ]);

        return $this->moduleTemplate->renderResponse('Configurations');
    }

    public function detail(ServerRequestInterface $request): ResponseInterface
    {
        $this->initializeModuleTemplate($request);
        $configurationUid = (int)($request->getQueryParams()['configuration'] ?? null);

        if ($configurationUid > 0) {
            $this->moduleTemplate->assignMultiple([
                'configuration' => $this->configurationRepository->getConfiguration($configurationUid),
            ]);
        }

        return $this->moduleTemplate->renderResponse('ConfigurationDetail');
    }
}
