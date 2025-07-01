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
use TYPO3\CMS\Core\Site\Entity\Site;
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

        $notification = new Notification($frontendUser->getUid(), $configuration);
        $type = $configuration->getType();
        $notification->setTitle($type . ' Notification');
        $notification->setUrl($this->createLink($configuration) ?? '');


        switch ($type) {
            case Type::DEFAULT:
            case Type::SUCCESS:
            case Type::ERROR:
            case Type::INFO:
            case Type::WARNING:


//                try {
//                    /** @var FileReference[] $fileObjects */
//                    $fileObjects = $this->fileRepository->findByRelation(Configuration::TABLE_NAME, Configuration::IMAGE_FIELD, $configuration->getUid());
//                    if(isset($fileObjects[0]) && $fileObjects[0] instanceof FileReference) {
//
//                        $file  = $fileObjects[0]->getOriginalFile()->getUid();
//
//
//                    }
//
//                } catch (FileDoesNotExistException $e) {
//                    // ... do some exception handling
//                }

                break;
            case Type::RECORDADDED:
            case Type::RECORDUPDATED:
                break;
            default:
                throw new \Exception('Notification type not supported');
        }

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


    public function createNotificationTranslation(Notification $notification, Configuration $translatedConfiguration, FrontendUser $frontendUser): Notification
    {
        $translation = $this->createNotification($translatedConfiguration, $frontendUser);
        $translation->setSysLanguageUid($translatedConfiguration->getSysLanguageUid());
        $translation->setL10nParent($notification->getUid());

        return $translation;
    }
}