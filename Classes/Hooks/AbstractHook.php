<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Hooks;

use TRAW\NotificationsFramework\Domain\DTO\Settings;
use TRAW\NotificationsFramework\Domain\Model\BackendUserInfo;
use TRAW\NotificationsFramework\Events\AbstractEvent;
use TYPO3\CMS\Core\Configuration\Features;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AbstractHook
{
    protected EventDispatcher $eventDispatcher;
    protected Settings $settings;


    public function __construct()
    {
        $features = GeneralUtility::makeInstance(Features::class);
        $this->eventDispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $this->settings = GeneralUtility::makeInstance(Settings::class, $features);
    }

    /**
     * @param AbstractEvent $event
     */
    protected function dispatchEvent(AbstractEvent $event)
    {
        $this->eventDispatcher->dispatch($event);
    }

    protected function getBeUserInfo(): BackendUserInfo
    {
        return new BackendUserInfo($GLOBALS['BE_USER']->user);
    }
}