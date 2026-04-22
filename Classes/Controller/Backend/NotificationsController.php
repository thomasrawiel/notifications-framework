<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TRAW\NotificationsFramework\Domain\Repository\NotificationRepository;
use TRAW\NotificationsFramework\Domain\Repository\ReferenceRepository;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TRAW\NotificationsFramework\Utility\TreeListUtility;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;

#[AsController]
final class NotificationsController extends AbstractController
{
    public function __construct(
        protected readonly ModuleTemplateFactory  $moduleTemplateFactory,
        protected readonly UriBuilder             $uriBuilder,
        protected readonly NotificationRepository $notificationRepository,
        protected readonly ReferenceRepository    $referenceRepository,
        protected readonly SettingsUtility        $settingsUtility,
        protected readonly TreeListUtility        $treeListUtility,
    )
    {
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->initializeModuleTemplate($request);
        $moduleData = $request->getAttribute('moduleData');
        $demand = [
            'sortField' => $moduleData->get('sortField'),
            'sortDirection' => in_array($moduleData->get('sortDirection'), ['asc', 'desc']) ? $moduleData->get('sortDirection') : 'asc',
            'filter' => is_array($moduleData->get('filter')) ? $moduleData->get('filter') : ['type' => '', 'valid' => ''],
            'uid' => null,
            'pid' => $this->settingsUtility->storeNotificationsOnRecordPid() ? $this->selectedPageUID : $this->treeListUtility->getTreeListArrayFromArray($this->settingsUtility->getNotificationStorage(), $this->settingsUtility->getNotificationStorageRecursive()),
            'currentPage' => (int)($moduleData->get('currentPage') > 0 ? $moduleData->get('currentPage') : 1),
            'perPage' => (int)($moduleData->get('perPage') > 0 ? $moduleData->get('perPage') : 10),
        ];

        $notifications = array_map(function ($notification) {


            return $notification;
        }, $this->notificationRepository->getNotificationsByDemand($demand));

        $this->applyFilters($notifications, $demand['filter']);
        $this->sortList($notifications, $demand['sortField'], $demand['sortDirection']);


        $this->moduleTemplate->assignMultiple($this->buildPagination($notifications, $demand['currentPage'], $demand['perPage']));
        $this->moduleTemplate->assignMultiple([
            'demand' => $demand,
            'action' => 'listNotifications',
            'filters' => [
                'type' => array_values(array_unique(
                    array_column($notifications, 'type')
                )),
            ],
        ]);


        return $this->moduleTemplate->renderResponse('Notifications');
    }

    public function detail(ServerRequestInterface $request): ResponseInterface
    {
        $this->initializeModuleTemplate($request);
        $notificationUid = (int)($request->getQueryParams()['notification'] ?? null);

        if ($notificationUid > 0) {
            $this->moduleTemplate->assignMultiple([
                'notification' => $this->notificationRepository->getnotification($notificationUid),
            ]);
        }

        return $this->moduleTemplate->renderResponse('NotificationDetail');
    }
}
