<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
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
class NotificationsConfigurationsController extends AbstractController
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

        $moduleData = $request->getAttribute('moduleData');
        if (
            (isset($request->getQueryParams()['sortField']) && $request->getQueryParams()['sortField'] !== $moduleData->get('sortField'))
            ||
            (isset($request->getQueryParams()['sortDirection']) && $request->getQueryParams()['sortDirection'] !== $moduleData->get('sortDirection'))
            ||
            (isset($request->getQueryParams()['perPage']) && (int)$request->getQueryParams()['perPage'] !== $moduleData->get('perPage'))

        ) {
            $moduleData->set('currentPage', 1);
        }


        $demand = [
            'sortField' => $moduleData->get('sortField'),
            'sortDirection' => in_array($moduleData->get('sortDirection'), ['asc', 'desc']) ? $moduleData->get('sortDirection') : 'asc',
            'uid' => null,
            'pid' => $this->settingsUtility->storeNotificationsOnRecordPid() ? $this->selectedPageUID : $this->treeListUtility->getTreeListArrayFromArray($this->settingsUtility->getNotificationStorage(), $this->settingsUtility->getNotificationStorageRecursive()),
            'perPage' => $moduleData->get('perPage'),
        ];

        $configurations = $this->configurationRepository->listConfigurations($demand);
        foreach ($configurations as $k => $config) {
            $configurations[$k]['valid'] = $this->configurationValidation->validate($config);
            if (!$config['hidden']) {
                $configurations[$k]['audience'] = $this->audienceUtility->getUsersCountFromConfiguration($this->configurationRepository->findByUid($config['uid']));
            } else {
                $configurations[$k]['audience'] = 0;
            }
            if ($config['record']) {
                $table = RecordUtility::getTableFromRecordString($config['record']);
                $recordUid = RecordUtility::getRecordUidAsIntegerFromRecordString($config['record']);
                $attachedRecord = BackendUtility::getRecord($table, $recordUid);
                $configurations[$k]['record'] = [
                    'uid' => $attachedRecord['uid'],
                    'pid' => $attachedRecord['pid'],
                    'table' => $table,
                    'row' => $attachedRecord,
                ];
            }
        }
        $configurations = $this->configurationRepository->sortList($configurations, $demand['sortField'], $demand['sortDirection']);

        $paginator = new ArrayPaginator($configurations, (int)$moduleData->get('currentPage'), (int)$moduleData->get('perPage'));
        $pagination = new SlidingWindowPagination($paginator, 20);

        $this->moduleTemplate->assignMultiple([
            'demand' => $demand,
            'action' => 'listConfigurations',
            'pagination' => $pagination,
            'paginator' => $paginator,
            'currentPage' => (int)$moduleData->get('currentPage'),
        ]);

        return $this->moduleTemplate->renderResponse('Backend/Configuration/List');
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

        return $this->moduleTemplate->renderResponse('Backend/Configuration/Detail');
    }
}
