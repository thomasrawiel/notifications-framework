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

        if (
            isset($request->getQueryParams()['sortField']) || isset($request->getParsedBody()['sortField'])
            || isset($request->getQueryParams()['sortDirection']) || isset($request->getParsedBody()['sortDirection'])
            || isset($request->getQueryParams()['filter']) || isset($request->getParsedBody()['filter'])
            || isset($request->getQueryParams()['perPage']) || isset($request->getParsedBody()['perPage'])
        ) {
            $moduleData->set('currentPage', 1);
        }

        $demand = [
            'sortField' => $moduleData->get('sortField'),
            'sortDirection' => in_array($moduleData->get('sortDirection'), ['asc', 'desc']) ? $moduleData->get('sortDirection') : 'asc',
            'filter' => is_array($moduleData->get('filter')) ? $moduleData->get('filter') : ['type'=>'','valid'=>''],
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
            return $configuration;
        }, $this->configurationRepository->getConfigurationsByDemand($demand));
        $applyFilters = array_filter($demand['filter'], static fn($value): bool => $value !== null && $value !== '');

        if ($applyFilters !== []) {
            $configurations = array_values(array_filter(
                $configurations,
                static function (array $configuration) use ($applyFilters): bool {
                    foreach ($applyFilters as $filter => $filterValue) {
                        if (!array_key_exists($filter, $configuration)) {
                            return false;
                        }

                        if ($configuration[$filter] != $filterValue) {
                            return false;
                        }
                    }

                    return true;
                }
            ));
        }
        $configurations = $this->configurationRepository->sortList($configurations, $demand['sortField'], $demand['sortDirection']);

        $paginator = new ArrayPaginator($configurations, $demand['currentPage'], $demand['perPage']);
        $pagination = new SlidingWindowPagination($paginator, 20);

        $this->moduleTemplate->assignMultiple([
            'demand' => $demand,
            'action' => 'listConfigurations',
            'pagination' => $pagination,
            'paginator' => $paginator,
            'filters' => [
                'type' => $GLOBALS['TCA']['tx_notifications_framework_configuration']['columns']['type']['config']['items'],
                'valid' => array_values(array_unique(
                    array_column($configurations, 'valid')
                )),

            ],
            'currentPage' => (int)$moduleData->get('currentPage'),
        ]);

        return $this->moduleTemplate->renderResponse('Configurations');
    }

    private function matchesFilter(array $configuration, string $filter, mixed $filterValue): bool
    {

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

        return $this->moduleTemplate->renderResponse('ConfigurationsDetail');
    }
}
