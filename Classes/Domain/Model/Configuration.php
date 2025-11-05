<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

final class Configuration extends AbstractEntity
{
    public const TABLE_NAME = 'tx_notifications_framework_configuration';
    public const IMAGE_FIELD = 'image';

    public const AUDIENCE = ['', 'users', 'groups', 'mixed'];

    protected bool $done = false;

    protected bool $push = false;

    protected string $targetAudience = '';

    protected string $type = '';

    protected string $feGroups = '';

    protected string $feUsers = '';

    protected string $title = '';

    protected string $label = '';

    protected string $message = '';

    protected int $image = 0;

    protected string $url = '';

    protected string $record = '';

    protected string $table = '';

    protected int $sysLanguageUid = 0;

    protected int $l10nParent = 0;


    public function isDone(): bool
    {
        return $this->done;
    }

    public function setDone(bool $done): void
    {
        $this->done = $done;
    }

    public function isPush(): bool
    {
        return $this->push;
    }

    public function setPush(bool $push): void
    {
        $this->push = $push;
    }

    public function getTargetAudience(): string
    {
        return $this->targetAudience;
    }

    public function setTargetAudience(string $targetAudience): void
    {
        $this->targetAudience = $targetAudience;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getFeGroups(): string
    {
        return $this->feGroups;
    }

    public function setFeGroups(string $feGroups): void
    {
        $this->feGroups = $feGroups;
    }

    public function getFeUsers(): string
    {
        return $this->feUsers;
    }

    public function setFeUsers(string $feUsers): void
    {
        $this->feUsers = $feUsers;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    public function getMessage(): string
    {
        return $this->message ?? '';
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getImage(): int
    {
        return $this->image;
    }

    public function setImage(int $image): void
    {
        $this->image = $image;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    public function getRecord(): string
    {
        return $this->record;
    }

    public function setRecord(string $record): void
    {
        $this->record = $record;
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    public function getSysLanguageUid(): int
    {
        return $this->_languageUid;
    }

    public function setSysLanguageUid(int $sysLanguageUid): void
    {
        $this->sysLanguageUid = $sysLanguageUid;
    }

    public function getL10nParent(): int
    {
        return $this->l10nParent;
    }

    public function setL10nParent(int $l10nParent): void
    {
        $this->l10nParent = $l10nParent;
    }
}
