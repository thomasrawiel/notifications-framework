<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TRAW\NotificationsFramework\Domain\Factory\NotificationFactory;
use TRAW\NotificationsFramework\Domain\Model\Notification;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Domain\Repository\FrontendUserRepository;
use TRAW\NotificationsFramework\Domain\Repository\NotificationRepository;
use TRAW\NotificationsFramework\Events\Data\BeforeNotificationAddedEvent;
use TRAW\NotificationsFramework\Utility\FilterUtility;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
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
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly EventDispatcher $eventDispatcher,
        private readonly NotificationFactory $notificationFactory
    )
    {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $configurations = FilterUtility::filterConfigurations($this->configurationRepository->findAll()->toArray());

        foreach ($configurations as $configuration) {
            $audience = FilterUtility::filterAudience($configuration);

            $users = [];

            if (!empty($audience['users'])) {
                $users = $this->frontendUserRepository->findUsersByUids($audience['users']);
            }

            if (!empty($audience['groups'])) {
                $groupUsers = $this->frontendUserRepository->findUsersByGroups($audience['groups']);
                $users = [...$users, ...$groupUsers];
            }

            $users = FilterUtility::filterUniqueByUid($users);

            foreach ($users as $user) {
                $notifications[] = new Notification($user->getUid(), $configuration);




                if (!$this->notificationRepository->notificationExists($notifications[0])) {
                    $event = $this->eventDispatcher->dispatch(new BeforeNotificationAddedEvent($notifications[0]));
                    if($event->isAddNotification()) {
                        $this->notificationRepository->add($notifications[0]);
                        $this->persistenceManager->persistAll();

                        $translations = $this->configurationRepository->getTranslations($configuration);
                        if($translations->count()) {

                        }
                    }
                }
            }

            // Mark configuration as done and persist
            $configuration->setDone(true);
            $this->configurationRepository->update($configuration);
            $this->persistenceManager->persistAll();
        }


        return Command::SUCCESS;
    }
}