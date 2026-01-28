<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Service;

use Psr\Http\Message\ServerRequestInterface;
use Reelworx\TYPO3\FakeFrontend\FakeFrontendService;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\TypoScriptAspect;
use TYPO3\CMS\Core\Context\UserAspect;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Http\Uri;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\NullSite;
use TYPO3\CMS\Core\Site\Entity\Site;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\SiteFinder;
use TYPO3\CMS\Core\TypoScript\PageTsConfig;
use TYPO3\CMS\Core\TypoScript\PageTsConfigFactory;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Frontend\Typolink\AbstractTypolinkBuilder;
use TYPO3\CMS\Frontend\Typolink\LinkFactory;
use TYPO3\CMS\Frontend\Typolink\UnableToLinkException;

class LinkService
{
    private ?ServerRequestInterface $request;

    public function __construct(private readonly \TYPO3\CMS\Core\LinkHandling\LinkService $linkService)
    {
    }

    public function createLink(Configuration $configuration, ?int $languageUid = null): ?string
    {
        try {
            $site = GeneralUtility::makeInstance(SiteFinder::class)
                ->getSiteByPageId($configuration->getPid()); // pick a site root page
        } catch (\Exception $e) {
            if ($e->getCode() === 1521716622) {
                throw new \Exception('Cannot create link because the PID ' . $configuration->getPid() . ' is invalid. Couldn\'t find a valid siteconfiguration to create links', 1521716622, $e);
            } else {
                throw $e;
            }
        }

        if ($site === null || $site instanceof NullSite) {
            return null;
        }

        //fake-frontend
        $this->createGlobals($site, $configuration, $languageUid);
        $controller = $this->bootFrontendController($site, [], $this->request);

        if (Type::isRecordType($configuration->getType())) {
            $identifier = $this->getLinkHandlerIdentifierFromTable($configuration->getTable(), $controller);
            $linkDetails = [
                'type' => 'record',
                'identifier' => $identifier,
                'uid' => (int)substr($configuration->getRecord(), strlen($configuration->getTable()) + 1),
            ];
        } else {
            $linkDetails = $this->linkService->resolve($configuration->getUrl());
        }
        //unset request, we dont need that anymore
        $this->request = null;

        if (!isset($linkDetails['type'], $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder'][$linkDetails['type']])) {
            return null;
        }
        $linkBuilder = GeneralUtility::makeInstance(
            $GLOBALS['TYPO3_CONF_VARS']['FE']['typolinkBuilder'][$linkDetails['type']],
            $controller->cObj,
            $controller
        );
        if (!$linkBuilder instanceof AbstractTypolinkBuilder) {
            // @todo: Add a proper interface.
            throw new \RuntimeException('Single link builder must extend AbstractTypolinkBuilder', 1646504471);
        }
        try {
            $configuration = [
                'forceAbsoluteUrl' => true,
                'linkAccessRestrictedPages' => true,
            ];
            $result = $linkBuilder->build($linkDetails, '', '', $configuration);
            $this->cleanupTSFE();
            return (new Uri($result->getUrl()))->__toString();
        } catch (UnableToLinkException $e) {
            $this->cleanupTSFE();
            return null;
        }
    }

    private function getLinkHandlerIdentifierFromTable(string $table, TypoScriptFrontendController $controller): ?string
    {
        $tsConfig = $this->getPageTsConfig($controller, $this->request)['TCEMAIN.']['linkHandler.'] ?? null;
        if ($tsConfig === null) {
            return null;
        }
        foreach ($tsConfig as $identifier => $linkhandlerConfig) {
            if (($linkhandlerConfig['configuration.']['table'] ?? '') === $table) {
                return str_ends_with($identifier, '.') ? rtrim($identifier, '.') : $identifier;
            }
        }

        return null;
    }

    private function createGlobals(Site $site, Configuration $configuration, ?int $languageUid = null): void
    {
        $fakeFrontend = new FakeFrontendService();
        if ($fakeFrontend->buildFakeFE($site->getRootPageId(), $languageUid ?? $configuration->getSysLanguageUid())) {
            $this->request = $GLOBALS['TYPO3_REQUEST'];
            $fakeFrontend->resetGlobals();
        } else {
            throw new \RuntimeException('Building fake FE failed', 0, $fakeFrontend->lastError);
        }
        unset($fakeFrontend);
    }

    //from ext:redirects
    protected function bootFrontendController(SiteInterface $site, array $queryParams, ServerRequestInterface $originalRequest): TypoScriptFrontendController
    {
        // Request without a matching site configuration can still have matching redirects and the $site already
        // contains a resolved site based on the target or a default one. If the request site is a NullSite, we
        // replace it here to ensure proper TypoScript loading, which is essential if no sys_template record exist
        // and extension like `b13/bolt` providing fake template rows. Without this, they could work properly.
        //
        // There is currently not a better way to pass this down, and is fixed in TYPO3 v13 due to a more extensive
        // rework and implementation of a TypoScript factory already.
        //
        // See https://forge.typo3.org/issues/103395
        if ($originalRequest->getAttribute('site') instanceof NullSite) {
            $originalRequest = $originalRequest
                ->withAttribute('site', $site)
                ->withAttribute('siteLanguage', $site->getDefaultLanguage());
        }

        // Ensure template parsing by setting TypoScriptAspect::$forcedTemplateParsing (required for TypoScript setup initialization)
        GeneralUtility::makeInstance(Context::class)
            ->setAspect('typoscript', GeneralUtility::makeInstance(TypoScriptAspect::class, true));

        $controller = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            GeneralUtility::makeInstance(Context::class),
            $site,
            $originalRequest->getAttribute('language', $site->getDefaultLanguage()),
            new PageArguments($site->getRootPageId(), '0', []),
            new FrontendUserAuthentication()
        );
        $controller->determineId($originalRequest);
        $controller->calculateLinkVars($queryParams);
        $newRequest = $controller->getFromCache($originalRequest);
        $controller->releaseLocks();
        $controller->newCObj($newRequest);
        if (!isset($GLOBALS['TSFE']) || !$GLOBALS['TSFE'] instanceof TypoScriptFrontendController) {
            $GLOBALS['TSFE'] = $controller;
        }
        if (!$GLOBALS['TSFE']->sys_page instanceof PageRepository) {
            $GLOBALS['TSFE']->sys_page = GeneralUtility::makeInstance(PageRepository::class);
        }
        return $controller;
    }

    //from ext:redirects
    private function cleanupTSFE(): void
    {
        $context = GeneralUtility::makeInstance(Context::class);
        $context->unsetAspect('language');
        $context->unsetAspect('typoscript');
        $context->unsetAspect('frontend.preview');
        unset($GLOBALS['TSFE']);
    }

    protected function getPageTsConfig(TypoScriptFrontendController $tsfe, ServerRequestInterface $request): array
    {
        $id = $tsfe->id;
        $runtimeCache = GeneralUtility::makeInstance(CacheManager::class)->getCache('runtime');
        $pageTsConfig = $runtimeCache->get('pageTsConfig-' . $id);
        if ($pageTsConfig instanceof PageTsConfig) {
            return $pageTsConfig->getPageTsConfigArray();
        }
        $fullRootLine = $tsfe->rootLine;
        ksort($fullRootLine);
        $site = $request->getAttribute('site') ?? new NullSite();
        $pageTsConfigFactory = GeneralUtility::makeInstance(PageTsConfigFactory::class);
        $pageTsConfig = $pageTsConfigFactory->create($fullRootLine, $site);
        $runtimeCache->set('pageTsConfig-' . $id, $pageTsConfig);
        return $pageTsConfig->getPageTsConfigArray();
    }
}
