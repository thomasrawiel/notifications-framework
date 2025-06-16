<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Notification extends AbstractEntity
{
    public const TABLE_NAME = 'tx_notifications_framework_domain_model_notification';

    protected string $title;

    protected int $feUser;

    protected int $configuration;

    protected bool $read = false;

    protected $pid = 0;

    public function __construct(
        int $feUser,
        Configuration $configuration,
    )
    {
        $this->feUser = $feUser;
        $this->configuration = $configuration->getUid();
        $this->pid = $configuration->getPid();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function setPid(?int $pid): void
    {
        $this->pid = $pid;
    }

    public function getFeUser(): int
    {
        return $this->feUser;
    }

    public function setFeUser(int $feUser): void
    {
        $this->feUser = $feUser;
    }

    public function getConfiguration(): int
    {
        return $this->configuration;
    }

    public function setConfiguration(int $configuration): void
    {
        $this->configuration = $configuration;
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    public function setRead(bool $read): void
    {
        $this->read = $read;
    }
}