<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Controller\Backend;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TRAW\NotificationsFramework\Domain\Repository\NotificationRepository;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TYPO3\CMS\Backend\Attribute\AsController;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;

#[AsController]
final class NotificationsController extends AbstractController
{
    public function __construct(
        protected readonly ModuleTemplateFactory   $moduleTemplateFactory,
        protected readonly UriBuilder              $uriBuilder,
        protected readonly NotificationRepository  $notificationRepository,
        protected readonly SettingsUtility         $settingsUtility,
    )
    {
    }

    public function handleRequest(ServerRequestInterface $request): ResponseInterface
    {
        $this->initializeModuleTemplate($request);



        return $this->moduleTemplate->renderResponse('Notifications');
    }
}
