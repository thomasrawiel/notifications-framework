<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Audience;

use TRAW\NotificationsFramework\Domain\Model\Configuration;

final class GetAdditionalUsersCsvEvent
{
    private string $additionalUsersCsv = '';

    public function __construct(private readonly Configuration $configuration)
    {
    }

    public function getAdditionalUsersCsv(): string
    {
        return $this->additionalUsersCsv;
    }

    public function setAdditionalUsersCsv(string $additionalUsersCsv): void
    {
        $this->additionalUsersCsv = $additionalUsersCsv;
    }
}
