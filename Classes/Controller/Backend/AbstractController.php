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
use TYPO3\CMS\Core\Type\Bitmask\Permission;

abstract class AbstractController
{
    /**
     * @var ModuleTemplate
     */
    protected ModuleTemplate $moduleTemplate;

    private array $pageRecord = [];

    protected int $pageUid = 0;

    protected function initializeModuleTemplate(ServerRequestInterface $request): void
    {
        $view = $this->moduleTemplateFactory->create($request);

        $queryParams = $request->getQueryParams();
        $parsedBody = $request->getParsedBody();
        $this->pageUid = (int)($parsedBody['id'] ?? $queryParams['id'] ?? 0);

        $this->pageRecord = BackendUtility::readPageAccess($this->pageUid, $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW)) ? : [];
        if ($this->pageRecord !== []) {
            $view->getDocHeaderComponent()->setMetaInformation($this->pageRecord);
        }
        $view->makeDocHeaderModuleMenu(['id' => $this->pageUid]);
        $this->moduleTemplate = $view;
    }

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
