<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ServerRequestInterface;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Type\Bitmask\Permission;

abstract class AbstractController
{
    /**
     * @var ModuleTemplate
     */
    protected ModuleTemplate $moduleTemplate;

    private array $pageRecord = [];

    protected int $selectedPageUID = 0;

    public function __construct(
        protected readonly ModuleTemplateFactory $moduleTemplateFactory,
        protected readonly SettingsUtility       $settingsUtility,
    )
    {
    }

    protected function initializeModuleTemplate(ServerRequestInterface $request): void
    {
        $view = $this->moduleTemplateFactory->create($request);

        if ($request->getQueryParams()['id'] ?? false) {
            $this->selectedPageUID = (int)$request->getQueryParams()['id'];
        }

        $this->pageRecord = BackendUtility::readPageAccess($this->selectedPageUID, $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW)) ? : [];
        if ($this->pageRecord !== []) {
            $view->getDocHeaderComponent()->setMetaInformation($this->pageRecord);
        }
        $view->makeDocHeaderModuleMenu(['id' => $this->selectedPageUID]);

        $moduleData = $request->getAttribute('moduleData');
        if (
            isset($request->getQueryParams()['sortField']) || isset($request->getParsedBody()['sortField'])
            || isset($request->getQueryParams()['sortDirection']) || isset($request->getParsedBody()['sortDirection'])
            || isset($request->getQueryParams()['filter']) || isset($request->getParsedBody()['filter'])
            || isset($request->getQueryParams()['perPage']) || isset($request->getParsedBody()['perPage'])
        ) {
            $moduleData->set('currentPage', 1);
        }

        $view->assignMultiple([
            'selectedPageUID' => $this->selectedPageUID,
            'module' => $request->getAttribute('module'),
            'showPidColumn' => $this->settingsUtility->storeNotificationsOnRecordPid(),
            'currentPage' => (int)$moduleData->get('currentPage'),
        ]);

        $this->moduleTemplate = $view;
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function applyFilters(array $objects, array $filterDemand): array
    {
        $applyFilters = array_filter($filterDemand, static fn($value): bool => $value !== null && $value !== '');

        if ($applyFilters === []) {
            return $objects;
        }

        return array_values(array_filter(
            $objects,
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

    protected function sortList(array $objects, string $sortField, string $sortDirection = 'asc'): array
    {
        $sortDirection = strtolower($sortDirection);

        if (!isset($objects[0][$sortField])) {
            $sortField = 'uid';
        }

        $modifier = $sortDirection === 'asc' ? 1 : -1;
        usort($objects, function ($a, $b) use ($sortField, $modifier) {
            $aValue = $a[$sortField] ?? null;
            $bValue = $b[$sortField] ?? null;

            if ($aValue === $bValue) {
                return 0;
            }
            if ($aValue === null) return 1;
            if ($bValue === null) return -1;
            return ($aValue < $bValue ? -1 : 1) * $modifier;
        });


        return $objects;
    }

    protected function buildPagination(array $items, int $currentPage, int $perPage, int $maxLinks = 20): array
    {
        $paginator = new ArrayPaginator($items, $currentPage, $perPage);
        $pagination = new SlidingWindowPagination($paginator, $maxLinks);

        return [
            'pagination' => $pagination,
            'paginator' => $paginator,
        ];
    }
}
