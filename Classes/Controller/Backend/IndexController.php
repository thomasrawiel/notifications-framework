<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Domain\Repository\NotificationRepository;
use TRAW\NotificationsFramework\Domain\Repository\ReferenceRepository;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TRAW\NotificationsFramework\Utility\TreeListUtility;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;

#[AsController]
final class IndexController extends AbstractController
{
    public function __construct(
        protected readonly ModuleTemplateFactory   $moduleTemplateFactory,
        protected readonly UriBuilder              $uriBuilder,
        protected readonly ConfigurationRepository $configurationRepository,
        protected readonly NotificationRepository  $notificationRepository,
        protected readonly ReferenceRepository     $referenceRepository,
        protected readonly SettingsUtility         $settingsUtility,
        protected readonly TreeListUtility         $treeListUtility,
    )
    {
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->initializeModuleTemplate($request);

        $configurations = $this->configurationRepository->getConfigurationsByDemand();
        $notifications = $this->notificationRepository->getNotificationsByDemand();
        $configurationPids = $this->countByPid($configurations);
        $configurationTypes = $this->countByType($configurations);
        $notificationPids = $this->countByPid($notifications);
        $notificationTypes = $this->countByType($notifications);
        $references = $this->referenceRepository->getReferencesByDemand();
        $referencePids = $this->countByPid($references);

        $this->moduleTemplate->assignMultiple([
            'configurationPids' => $configurationPids,
            'notificationPids' => $notificationPids,
            'referencePids' => $referencePids,
            'configurationTypes' => $configurationTypes,
            'notificationTypes' => $notificationTypes,
        ]);

        return $this->moduleTemplate->renderResponse('Index');
    }
}
