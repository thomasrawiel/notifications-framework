<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Configuration;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Service\RateLimitService;
use TRAW\NotificationsFramework\Service\SpamCheckService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class BeforeConfigurationAddedEventListener
{
    public function __invoke(BeforeConfigurationAddedEvent $event)
    {
        $newId = $event->getNewId();
        $data = $event->getData();
        $record = $data[Configuration::TABLE_NAME][$newId] ?? null;

        if ($record === null || $event->isAddConfiguration() === false) {
            return;
        }
    }
}
