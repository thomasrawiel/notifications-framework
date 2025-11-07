<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Factory;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Model\Notification;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Service\LinkService;
use TRAW\NotificationsFramework\Utility\ImageUtility;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;

class NotificationFactory
{
    public function __construct(
        private readonly FileRepository $fileRepository,
        private readonly ImageUtility   $imageUtility,
        private readonly LinkService    $linkService,
    )
    {
    }

    public function createNotification(Configuration $configuration, FrontendUser $frontendUser): Notification
    {
        $type = $configuration->getType();
        if (!Type::isValidType($type)) {
            throw new \Exception('Notification type not supported');
        }

        $notification = new Notification($frontendUser->getUid(), $configuration);
        $notification->setTitle($type . ' Notification');
        $notification->setUrl($this->createLink($configuration));

        return $notification;
    }

    protected function createLink(Configuration $configuration, ?int $languageUid = null): string
    {
        if (empty($configuration->getUrl()) && empty($configuration->getRecord())) {
            return '';
        }
        return $this->linkService->createLink($configuration, $languageUid) ?? '';
    }

    public function createNotificationTranslation(Notification $notification, Configuration $translatedConfiguration, FrontendUser $frontendUser, ?int $languageUid = null): Notification
    {
        $targetLanguageUid = null;

        if ($translatedConfiguration->getSysLanguageUid() > 0) {
            $targetLanguageUid = $translatedConfiguration->getSysLanguageUid();
        } elseif ($translatedConfiguration->isAutotranslate() && $languageUid !== null) {
            $targetLanguageUid = $languageUid;
        }

        if ($targetLanguageUid !== null) {
            $translation = $this->createNotification($translatedConfiguration, $frontendUser);
            $translation->setSysLanguageUid($targetLanguageUid);
            $translation->setL10nParent($notification->getUid());
            $translation->setUrl($this->createLink($translatedConfiguration, $targetLanguageUid));
        }

        return $translation;
    }
}
