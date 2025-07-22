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
use TRAW\NotificationsFramework\Events\Data\BeforeNotificationAddedEvent;
use TRAW\NotificationsFramework\Utility\FilterUtility;
use TRAW\NotificationsFramework\Utility\ImageUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Http\NormalizedParams;
use TYPO3\CMS\Core\Http\ServerRequestFactory;
use TYPO3\CMS\Core\Localization\LanguageServiceFactory;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
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
        private readonly FrontendUserRepository      $frontendUserRepository,
        private readonly NotificationRepository      $notificationRepository,
        private readonly PersistenceManagerInterface $persistenceManager,
        private readonly EventDispatcher             $eventDispatcher,
        private readonly NotificationFactory         $notificationFactory,
        private readonly FileRepository              $fileRepository,
        private readonly ImageUtility                $imageUtility,
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
                $notification = $this->notificationFactory->createNotification($configuration, $user);

                if (!$this->notificationRepository->notificationExists($notification)) {
                    $event = $this->eventDispatcher->dispatch(new BeforeNotificationAddedEvent($notification));
                    if ($event->isAddNotification()) {

                        if ($configuration->getImage()) {
                            $fileReference = $this->fileRepository->findByRelation(Configuration::TABLE_NAME, Configuration::IMAGE_FIELD, $configuration->getUid());
                            if (isset($fileReference[0]) && $fileReference[0] instanceof FileReference) {
                                $this->imageUtility->createFileReferenceForNotification($notification, $fileReference[0]);
                            }

                        }


                        $this->notificationRepository->add($notification);
                        $this->persistenceManager->persistAll();

                        if ($configuration->getImage()) {
                            $fileReference = $this->fileRepository->findByRelation(Configuration::TABLE_NAME, Configuration::IMAGE_FIELD, $configuration->getUid());
                            if (isset($fileReference[0]) && $fileReference[0] instanceof FileReference) {
                                $this->imageUtility->createFileReferenceForNotification($notification, $fileReference[0]);
                            }

                        }


                        $translations = $this->configurationRepository->getTranslations($configuration);
                        if ($translations->count()) {
                            foreach ($translations as $translation) {
                                $this->notificationRepository->add(
                                    $this->notificationFactory->createNotificationTranslation($notification, $translation, $user),
                                );
                            }
                            $this->persistenceManager->persistAll();
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