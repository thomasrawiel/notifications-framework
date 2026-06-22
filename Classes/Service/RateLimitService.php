<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Service;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Repository\ConfigurationRepository;
use TRAW\NotificationsFramework\Events\Configuration\BeforeConfigurationAddedEvent;
use TRAW\NotificationsFramework\Utility\SettingsUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;

class RateLimitService
{
    public function __construct(
        private readonly SettingsUtility         $settingsUtility,
        private readonly ConnectionPool          $connectionPool,
        private readonly ConfigurationRepository $configurationRepository
    )
    {

    }

    public function spamCheck(BeforeConfigurationAddedEvent $event): void
    {
        $newId = $event->getNewId();
        $data = $event->getData()[Configuration::TABLE_NAME][$newId] ?? null;

        if ($data === null) {
            $event->setAddConfiguration(false);
            return;
        }

        $demand = [
            'type' => $data['type'],
            'pid' => $data['pid'],
            'record' => $data['record'],
            'target_audience' => $data['target_audience'],
            'maxitems' => 1,
            'sortField' => 'tstamp',
            'sortDirection' => 'desc',
        ];

        if (isset($data['fe_users'])) {
            $demand['fe_users'] = $data['fe_users'];
        }
        if (isset($data['fe_groups'])) {
            $demand['fe_groups'] = $data['fe_groups'];
        }

        $previousOfSameType = $this->configurationRepository->getConfigurationsByDemand($demand);
        if ($previousOfSameType !== []) {
            $latestRecord = $previousOfSameType[0];

            $latest = (int)($latestRecord['tstamp'] ?? 0);
            $current = (int)($data['tstamp'] ?? time());

            $threshold = 300;

            if ($current < $latest + $threshold) {
                $event->setAddConfiguration(false);
                return;
            }
        }

        $event->setAddConfiguration(true);
    }
}
