<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Audience;

use TRAW\NotificationsFramework\Domain\Model\Configuration;

final class GetAdditionalGroupsCsvEvent
{
    private string $additionalGroupsCsv = '';

    public function __construct(private readonly Configuration $configuration)
    {
    }

    public function getAdditionalGroupsCsv(): string
    {
        return $this->additionalGroupsCsv;
    }

    public function getConfiguration(): Configuration
    {
        return $this->configuration;
    }

    public function setAdditionalGroupsCsv(string $additionalGroupsCsv): void
    {
        $this->additionalGroupsCsv = $additionalGroupsCsv;
    }
}
