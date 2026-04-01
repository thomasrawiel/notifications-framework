<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Service;

use TRAW\NotificationsFramework\Domain\Factory\NotificationFactory;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Model\Notification;
use TRAW\NotificationsFramework\Domain\Model\Reference;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Domain\Repository\NotificationRepository;
use TRAW\NotificationsFramework\Domain\Repository\ReferenceRepository;
use TRAW\NotificationsFramework\Events\Data\BeforeNotificationAddedEvent;
use TRAW\NotificationsFramework\Events\Data\NotificationAllowedForUserEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class NotificationService
{
    public function __construct(
        private readonly ConfigurationRepository $configurationRepository,
        private readonly NotificationRepository  $notificationRepository,
        private readonly ReferenceRepository     $referenceRepository,
        private readonly NotificationFactory     $notificationFactory,
        private readonly PersistenceManager      $persistenceManager,
        private readonly EventDispatcher         $eventDispatcher,
    )
    {
    }

    public function createNotification(?Configuration $configuration = null): Notification
    {
        if ($this->notificationRepository->notificationExists($configuration?->getUid() ?? 0)) {
            return $this->notificationRepository->findByConfiguration($configuration->getUid())->getFirst();
        }

        $event = $this->eventDispatcher
            ->dispatch(new BeforeNotificationAddedEvent (
                $this->notificationFactory->createNotification($configuration),
                $configuration
            ));

        $notification = $event->getNotification();
        $this->persistNotification($notification);

        $translations = $this->configurationRepository->getTranslations($configuration);
        $translationsDone = [$notification->getSysLanguageUid()]; // we already saved this
        if ($translations->count()) {
            foreach ($translations as $translation) {
                $translatedNotification = $this->notificationFactory->createNotificationTranslation($notification, $translation, $translation->getSysLanguageUid());
                $event = $this->eventDispatcher
                    ->dispatch(new BeforeNotificationAddedEvent (
                        $translatedNotification,
                        $configuration
                    ));
                $this->persistNotification($event->getNotification());
                $translationsDone[] = $translatedNotification->getSysLanguageUid();
            }
        }
        //fill translations when autotranslate=1 with the content from the default language
        if ($configuration->isAutotranslate()) {
            $site = GeneralUtility::makeInstance(SiteFinder::class)
                ->getSiteByPageId($configuration->getPid());
            foreach ($site->getAllLanguages() as $language) {
                if (in_array($language->getLanguageId(), $translationsDone, true)) {
                    //skip, because we already have that one
                    continue;
                }
                $translatedNotification = $this->notificationFactory->createNotificationTranslation(
                    $notification, $configuration, $language->getLanguageId());
                $event = $this->eventDispatcher
                    ->dispatch(new BeforeNotificationAddedEvent (
                        $translatedNotification,
                        $configuration
                    ));
                $this->persistNotification($event->getNotification());
            }
        }

        return $notification;
    }

    public function createReference(Notification $notification, FrontendUser $frontendUser, Configuration $configuration): void
    {
        $event = $this->eventDispatcher->dispatch(new NotificationAllowedForUserEvent($notification, $frontendUser));
        if ($event->isAllowed() === false) {
            return;
        }

        if($this->referenceRepository->referenceExists($notification->getUid(), $frontendUser->getUid())) {
            return;
        }

        $reference = new Reference($notification->getUid(), $frontendUser->getUid());
        $reference->setPid($notification->getPid());

        $this->persistReference($reference);

        $translations = $this->configurationRepository->getTranslations($configuration);
        $translationsDone = [$configuration->getSysLanguageUid()]; //
        if ($translations->count()) {
            foreach ($translations as $translation) {
                $translatedReference = new Reference($notification->getUid(), $frontendUser->getUid());
                $translatedReference->setPid($notification->getPid());
                $translatedReference->setL10nParent($reference->getUid());
                $translatedReference->setSysLanguageUid($translation->getSysLanguageUid());

                $this->referenceRepository->add($translatedReference);
                $translationsDone[] = $translatedReference->getSysLanguageUid();
            }
        }
        //fill translations when autotranslate=1 with the content from the default language
        if ($configuration->isAutotranslate()) {
            $site = GeneralUtility::makeInstance(SiteFinder::class)
                ->getSiteByPageId($configuration->getPid());
            foreach ($site->getAllLanguages() as $language) {
                if (in_array($language->getLanguageId(), $translationsDone, true)) {
                    //skip, because we already have that one
                    continue;
                }
                $translatedReference = new Reference($notification->getUid(), $frontendUser->getUid());
                $translatedReference->setPid($notification->getPid());
                $translatedReference->setL10nParent($reference->getUid());
                $translatedReference->setSysLanguageUid($language->getLanguageId());

                $this->referenceRepository->add($translatedReference);
            }
        }
        $this->persistenceManager->persistAll();
    }

    private function persistNotification($notification): void
    {
        $this->notificationRepository->add($notification);
        $this->persistenceManager->persistAll();
    }

    private function persistReference($reference): void
    {
        $this->referenceRepository->add($reference);
        $this->persistenceManager->persistAll();
    }
}
