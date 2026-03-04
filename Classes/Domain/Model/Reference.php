<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model;

use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Model\Notification;

class Reference
{
    /**
     * @var \TRAW\NotificationsFramework\Domain\Model\FrontendUser
     */
    protected FrontendUser $user;
    /**
     * @var \TRAW\NotificationsFramework\Domain\Model\Notification
     */
    protected Notification $notification;

    public const TABLE_NAME = 'tx_notifications_framework_domain_model_notification_reference';

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

    public function getUser(): FrontendUser
    {
        return $this->user;
    }

    public function setUser(FrontendUser $user): void
    {
        $this->user = $user;
    }

    public function getNotification(): Notification
    {
        return $this->notification;
    }

    public function setNotification(Notification $notification): void
    {
        $this->notification = $notification;
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
