<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;

#[AsController]
final class NotificationsIndexController extends AbstractController
{
    public function __construct(
        protected readonly ModuleTemplateFactory   $moduleTemplateFactory,
        protected readonly UriBuilder              $uriBuilder,
        protected readonly ConfigurationRepository $configurationRepository,
        protected readonly SettingsUtility         $settingsUtility,
    )
    {
    }
    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->initializeModuleTemplate($request);
//        $backendUser = $this->getBackendUser();
//        $currentModule = $request->getAttribute('module');
//        $currentModuleIdentifier = $currentModule->getIdentifier();
////        $pageId = (int)($request->getQueryParams()['id'] ?? 0);
////        $pageRecord = BackendUtility::readPageAccess($pageId, $backendUser->getPagePermsClause(Permission::PAGE_SHOW)) ?: [];
//
//        $moduleData = $request->getAttribute('moduleData');
//        if ($moduleData->cleanUp([])) {
//            $backendUser->pushModuleData($currentModuleIdentifier, $moduleData->toArray());
//        }

        if ($request->getQueryParams()['id']) {
            $this->selectedPageUID = (int)$request->getQueryParams()['id'];
        }

        return $this->moduleTemplate->renderResponse('Backend/Index');
    }

    public function listInvalidConfigurationsAction(): ResponseInterface
    {
        return $this->moduleTemplate->renderResponse('Backend/ListInvalidConfigurations');
    }

    public function detailConfigurationAction(): ResponseInterface
    {
        return $this->moduleTemplate->renderResponse('Backend/DetailConfiguration');
    }

    public function notificationtsAction(): ResponseInterface
    {
        return $this->moduleTemplate->renderResponse('Backend/Notificatios');
    }
}
