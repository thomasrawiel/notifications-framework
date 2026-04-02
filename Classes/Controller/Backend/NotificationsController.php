<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\Attribute\AsController;

#[AsController]
final class NotificationsController extends ActionController
{
    /**
     * @var ModuleTemplate
     */
    protected ModuleTemplate $moduleTemplate;

    private array $pageRecord = [];

    /**
     * @param ModuleTemplateFactory $moduleTemplateFactory
     * @param WidgetRepository      $widgetRepository
     */
    public function __construct(
        private readonly ModuleTemplateFactory   $moduleTemplateFactory,
        private readonly ConfigurationRepository $configurationRepository,
        private readonly SettingsUtility         $settingsUtility,
        protected ?int                           $selectedPageUID = null,
    )
    {
        $this->selectedPageUID = $selectedPageUID ?? 0;
    }

    /**
     * @return void
     */
    protected function initializeAction(): void
    {
        $this->moduleTemplate = $this->initializeModuleTemplate();

        if ($this->request->hasArgument('id')) {
            $this->selectedPageUID = (int)$this->request->getArgument('id');
        }

        parent::initializeAction();
    }

    private function initializeModuleTemplate(): ModuleTemplate
    {
        $view = $this->moduleTemplateFactory->create($this->request);
        $menuItems = [
            'index' => ['action' => 'index', 'label' => 'Overview',],
            'configurations' => ['action' => 'listConfigurations', 'label' => 'Configurations',],
            'notifications' => ['action' => 'listNotifications', 'label' => 'Notifications',],
        ];
        $menu = $view->getDocHeaderComponent()->getMenuRegistry()->makeMenu();
        $menu->setIdentifier('component_action_menu')->setLabel('something');
        foreach ($menuItems as $item) {
            $isActive = $this->request->getControllerActionName() === $item['action'];
            $menu->addMenuItem(
                $menu->makeMenuItem()
                    ->setTitle($item['label'])
                    ->setActive($isActive)
                    ->setHref($this->uriBuilder->reset()->uriFor($item['action'], [], $this->request->getControllerName(), $this->request->getControllerExtensionName(), $this->request->getPluginName()))
            );
            if ($isActive) {
                $context = $item['label'];
            }
        }
        $view->getDocHeaderComponent()->getMenuRegistry()->addMenu($menu);
        // $view->setTitle($this->getTitle(), $context);

        $this->pageRecord = BackendUtility::readPageAccess($this->selectedPageUID, $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW)) ? : [];
        if ($this->pageRecord !== []) {
            $view->getDocHeaderComponent()->setMetaInformation($this->pageRecord);
        }
        $view->setFlashMessageQueue($this->getFlashMessageQueue());

        return $view;
    }

    public function indexAction(): ResponseInterface
    {
        return $this->moduleTemplate->renderResponse('Index');
    }

    public function listConfigurationsAction(): ResponseInterface
    {
        $defaultDemand = [
            'sortField' => 'uid',
            'sortDirection' => 'desc',
            'uid' => null,
            'pid' => $this->settingsUtility->storeNotificationsOnRecordPid() ? $this->selectedPageUID : $this->settingsUtility->getNotificationStorage(),
        ];
        $demand = $this->request->hasArgument('demand')
            ? $this->request->getArgument('demand') : $defaultDemand;

        $this->moduleTemplate->assignMultiple([
            'demand' => $demand,
            'action' => 'listConfigurations',
            'showPidColumn' => $this->settingsUtility->storeNotificationsOnRecordPid(),
            'configurations' => $this->configurationRepository->listConfigurations($demand),
        ]);

        return $this->moduleTemplate->renderResponse('ListConfigurations');
    }

    public function detailConfigurationAction(): ResponseInterface
    {
        return $this->moduleTemplate->renderResponse('DetailConfiguration');
    }

    public function notificaiontsAction(): ResponseInterface
    {
        return $this->moduleTemplate->renderResponse('Notificatios');
    }

    private function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }
}
