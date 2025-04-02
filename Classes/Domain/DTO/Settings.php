<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\DTO;

use TYPO3\CMS\Core\Configuration\Features;

final class Settings {
    private $afterDatabaseOperations;

    public function __construct(private Features $features) {
        $this->afterDatabaseOperations = $this->features->isFeatureEnabled('dispatchEventAfterDatabaseOperations');
    }

    public function getAfterDatabaseOperations(): bool
    {
        return $this->afterDatabaseOperations;
    }
}