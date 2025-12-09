<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Service;

use TRAW\NotificationsFramework\Domain\Factory\NotificationFactory;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Domain\Repository\NotificationRepository;
use TRAW\NotificationsFramework\Events\Data\BeforeNotificationAddedEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepository  $notificationRepository,
        private readonly ConfigurationRepository $configurationRepository,
        private readonly NotificationFactory     $notificationFactory,
        private readonly PersistenceManager      $persistenceManager,
        private readonly EventDispatcher         $eventDispatcher,
    )
    {
    }

    public function createNotification(AbstractEntity $user, ?Configuration $configuration = null): void
    {
        $notification = $this->notificationFactory->createNotification($configuration, $user);

        if (!$this->notificationRepository->notificationExists($notification)) {
            $event = $this->eventDispatcher->dispatch(new BeforeNotificationAddedEvent($notification, $configuration));
            $notification = $event->getNotification();
            if ($event->isAddNotification()) {
                $this->notificationRepository->add($notification);
                $this->persistenceManager->persistAll();

                $translations = $this->configurationRepository->getTranslations($configuration);
                $translationsDone = [$notification->getSysLanguageUid()]; // we already saved this
                if ($translations->count()) {
                    foreach ($translations as $translation) {
                        $translatedNotification = $this->notificationFactory->createNotificationTranslation($notification, $translation, $user, $translation->getSysLanguageUid());
                        $event = $this->eventDispatcher->dispatch(new BeforeNotificationAddedEvent($translatedNotification, $configuration));
                        $this->notificationRepository->add($event->getNotification());
                        $this->persistenceManager->persistAll();
                        $translationsDone[] = $translatedNotification->getSysLanguageUid();
                    }
                }
                //fill translations when autotranslate=1 with the content from the default language
                if ($configuration->isAutotranslate()) {
                    $site = GeneralUtility::makeInstance(SiteFinder::class)
                        ->getSiteByPageId($configuration->getPid());
                    foreach ($site->getAllLanguages() as $language) {
                        if (in_array($language->getLanguageId(), $translationsDone)) {
                            //skip, because we already have that one
                            continue;
                        }
                        $translatedNotification = $this->notificationFactory->createNotificationTranslation(
                            $notification, $configuration, $user, $language->getLanguageId());
                        $event = $this->eventDispatcher->dispatch(new BeforeNotificationAddedEvent($translatedNotification, $configuration));
                        $this->notificationRepository->add($event->getNotification());
                        $this->persistenceManager->persistAll();
                    }
                }
            }
        }
    }
}
