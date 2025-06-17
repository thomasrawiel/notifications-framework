<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TRAW\NotificationsFramework\Domain\Factory\NotificationFactory;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Domain\Repository\FrontendUserRepository;
use TRAW\NotificationsFramework\Domain\Repository\NotificationRepository;
use TRAW\NotificationsFramework\Utility\FilterUtility;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;

#[AsCommand(
    name: 'notifications:generate',
    description: 'Generates Notifications from existing Notification configurations.',
)]
final class GenerateNotificationsCommand extends Command
{

    public function __construct(
        private readonly ConfigurationRepository     $configurationRepository,
        private readonly FrontendUserRepository      $frontendUserRepository,
        private readonly NotificationRepository      $notificationRepository,
        private readonly PersistenceManagerInterface $persistenceManager
    )
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $configurations = FilterUtility::filterConfigurations($this->configurationRepository->findAll()->toArray());

        foreach ($configurations as $configuration) {
            $audience = FilterUtility::filterAudience($configuration);

            if ($audience['groups']) {
                $users = $this->frontendUserRepository->findUsersByGroups($audience['groups']);
                $audience['users'] = array_merge($audience['users'] ?? [], $users);
            }

            $audience['users'] = FilterUtility::filterUniqueByUid($audience['users']);

            foreach ($audience['users'] as $user) {
                $notification = NotificationFactory::createNotification($configuration, $user);
                if (!$this->notificationRepository->notificationExists($notification)) {
                    $this->notificationRepository->add($notification);
                }
            }

            $configuration->setDone(true);
            $this->configurationRepository->update($configuration);
            $this->persistenceManager->persistAll();
        }


        return Command::SUCCESS;
    }
}