<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Service\NotificationService;
use TRAW\NotificationsFramework\Utility\AudienceUtility;
use TRAW\NotificationsFramework\Utility\FilterUtility;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

#[AsCommand(
    name: 'notifications:generate',
    description: 'Generates Notifications from existing Notification configurations.',
)]
final class GenerateNotificationsCommand extends Command
{

    public function __construct(
        private readonly ConfigurationRepository     $configurationRepository,
        private readonly NotificationService         $notificationService,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly AudienceUtility             $audienceUtility,
    )
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (Environment::isCli()) {
            $_SERVER['HTTP_HOST'] = 'example.org';
            $_SERVER['REQUEST_URI'] = '/';
            $_SERVER['SCRIPT_NAME'] = '/index.php';
            $_SERVER['SERVER_PORT'] = '443';
            $_SERVER['HTTPS'] = 'on';

            Bootstrap::initializeBackendAuthentication();
            $GLOBALS['LANG'] = GeneralUtility::makeInstance(LanguageServiceFactory::class)
                ->createFromUserPreferences($GLOBALS['BE_USER']);
        }

        $configurations = FilterUtility::filterConfigurations($this->configurationRepository->findAll()->toArray());
        foreach ($configurations as $configuration) {
            $users = $this->audienceUtility->getUsersFromConfiguration($configuration);

            foreach ($users as $user) {
                $this->notificationService->createNotification($user, $configuration);
            }

            // Mark configuration as done and persist
            $configuration->setDone(true);
            $this->configurationRepository->update($configuration);
            $this->persistenceManager->persistAll();
        }


        return Command::SUCCESS;
    }
}
