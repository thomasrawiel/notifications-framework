<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Backend\Attribute\AsController;

#[AsController]
class ConfigurationsController extends ActionController
{
    /**
     * @var ModuleTemplate
     */
    protected ModuleTemplate $moduleTemplate;

    /**
     * @param ModuleTemplateFactory $moduleTemplateFactory
     * @param WidgetRepository      $widgetRepository
     */
    public function __construct(
        private readonly ModuleTemplateFactory   $moduleTemplateFactory,
        private readonly ConfigurationRepository $configurationRepository,
        private readonly SettingsUtility         $settingsUtility
    )
    {
    }

    /**
     * @return void
     */
    protected function initializeAction(): void
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($this->request);
    }

    public function listConfigurationsAction(): ResponseInterface
    {
        $defaultDemand = ['sortField' => 'installs', 'sortDirection' => 'desc'];
        $demand = $this->request->hasArgument('demand') ? $this->request->getArgument('demand') : $defaultDemand;

        $this->moduleTemplate->assignMultiple([
            'demand' => $demand,
            'action' => 'listConfigurations',
            'showPidColumn' => $this->settingsUtility->storeNotificationsOnRecordPid(),
            'configurations' => $this->configurationRepository->listConfigurations($demand),
        ]);

        return $this->moduleTemplate->renderResponse('ListConfigurations');
    }

    public function detailConfigurationAction(): ResponseInterface {
        return $this->moduleTemplate->renderResponse('DetailConfiguration');
    }
}
