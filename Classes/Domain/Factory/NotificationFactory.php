<?php

namespace TRAW\NotificationsFramework\Domain\Factory;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Model\Notification;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Utility\ImageUtility;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\LinkHandling\LinkService;
use TYPO3\CMS\Core\LinkHandling\TypoLinkCodecService;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Resource\FileRepository;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class NotificationFactory
{
    public function __construct(
        private readonly FileRepository       $fileRepository,
        private readonly ImageUtility         $imageUtility,
        private readonly UriBuilder           $uriBuilder,
        private readonly LinkService          $linkService,
        private readonly TypoLinkCodecService $typoLinkCodecService,
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
        $notification->setUrl($this->createLink($configuration) ?? '');



        return $notification;
    }

    protected function createLink($configuration): ?string
    {
        if (empty($configuration->getUrl())) {
            return null;
        }

        $siteFinder = GeneralUtility::makeInstance(SiteFinder::class);
        $site = $siteFinder->getSiteByPageId($configuration->getPid()); // pick a site root page
        $language = $site->getDefaultLanguage();

        $url = '';

        try {
            $typoLinkConfiguration = $this->typoLinkCodecService->decode($configuration->getUrl());
            $linkResult = $this->linkService->resolve($configuration->getUrl(), $site, $language);
            //$url = $linkResult->getUrl();
            $cObj = $this->getContentObjectRenderer($site);
            $url = $cObj->typoLink_URL(['parameter' => $typoLinkConfiguration['url'], 'forceAbsoluteUrl' => true]);

        } catch (\Throwable $e) {

        }

        return $url;
    }

    private function getContentObjectRenderer(Site $site): ContentObjectRenderer
    {
        $request = (new ServerRequest())
            ->withAttribute('applicationType', SystemEnvironmentBuilder::REQUESTTYPE_FE)
            ->withAttribute('site', $site);

        $cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
        $cObj->setRequest($request);

        return $cObj;
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
        }

        return $translation;
    }
}
