<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model;

use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Model\Notification;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

class Reference extends AbstractEntity
{

    public const TABLE_NAME = 'tx_notifications_framework_domain_model_notification_reference';
    /**
     * @var int
     */
    protected int $l10nParent = 0;
    /**
     * @var int
     */
    protected int $read = 0;

    /**
     * @var int
     */
    protected int $readDate = 0;
    /**
     * @var int
     */
    protected int $tstamp = 0;

    protected int $sysLanguageUid;

    public function __construct(protected int $notification, protected int $feUser)
    {
    }

    public function getNotification(): int
    {
        return $this->notification;
    }

    public function setNotification(int $notification): void
    {
        $this->notification = $notification;
    }

    public function getFeUser(): int
    {
        return $this->feUser;
    }

    public function setFeUser(int $feUser): void
    {
        $this->feUser = $feUser;
    }

    public function getSysLanguageUid(): int
    {
        return $this->sysLanguageUid;
    }

    public function setSysLanguageUid(int $sysLanguageUid): void
    {
        $this->sysLanguageUid = $sysLanguageUid;
        $this->_languageUid = $sysLanguageUid;
    }

    public function getL10nParent(): int
    {
        return $this->l10nParent;
    }

    public function setL10nParent(int $l10nParent): void
    {
        $this->l10nParent = $l10nParent;
    }

    public function getRead(): int
    {
        return $this->read;
    }

    public function setRead(int $read): void
    {
        $this->read = $read;
    }

    public function getReadDate(): int
    {
        return $this->readDate;
    }

    public function setReadDate(int $readDate): void
    {
        $this->readDate = $readDate;
    }

    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    public function setTstamp(int $tstamp): void
    {
        $this->tstamp = $tstamp;
    }
}
