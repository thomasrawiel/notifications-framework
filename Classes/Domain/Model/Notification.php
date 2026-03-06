<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Class Notification
 * @package TRAW\NotificationsFramework\Domain\Model
 */
class Notification extends AbstractEntity
{
    /**
     *
     */
    public const TABLE_NAME = 'tx_notifications_framework_domain_model_notification';
    public const IMAGE_FIELD = 'image';

    /**
     * @var string
     */
    protected string $title;

    /**
     * @var int|null
     */
    protected int $configuration;

    /**
     * @var string
     */
    protected string $message;
    /**
     * @var string
     */
    protected string $label;
    /**
     * @var string
     */
    protected string $url;
    /**
     * @var string
     */
    protected string $media;

    /**
     * @var int
     */
    protected int $sysLanguageUid = 0;
    /**
     * @var int
     */
    protected int $l10nParent = 0;

    protected string $type = '';
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

    /**
     * @var int|null
     */
    protected $pid = 0;

    /**
     * @param Configuration $configuration
     */
    public function __construct(
        ?Configuration $configuration,
        array          $data = []
    )
    {
        if (empty($configuration) && empty($data)) {
            throw new \Exception('Configuration and/or data are required to create a notification.');
        }

        if (empty($configuration)) {
            $this->pid = $data['pid'];
            $this->label = $data['label'];
            $this->message = $data['message'];
            $this->configuration = 0;
            $this->type = $data['type'];
            $this->image = $data['image'];
        } else {
            $this->pid = $configuration->getPid();
            $this->label = $configuration->getLabel();
            $this->message = $configuration->getMessage();
            $this->configuration = $configuration->getUid();
            $this->type = $configuration->getType();
            $this->image = $configuration->getImage();
        }
        $this->tstamp = time();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     *
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @return int|null
     */
    public function getPid(): ?int
    {
        return $this->pid;
    }

    /**
     * @param int|null $pid
     *
     * @return void
     */
    public function setPid(?int $pid): void
    {
        $this->pid = $pid;
    }

    /**
     * @return int
     */
    public function getConfiguration(): int
    {
        return $this->configuration;
    }

    /**
     * @param int $configuration
     *
     * @return void
     */
    public function setConfiguration(int $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * @return int
     */
    public function getRead(): int
    {
        return $this->read;
    }

    /**
     * @param int $read
     *
     * @return void
     */
    public function setRead(int $read): void
    {
        $this->read = $read;
    }

    /**
     * @return int
     */
    public function getReadDate(): int
    {
        return $this->readDate;
    }

    /**
     * @param int $readDate
     *
     * @return void
     */
    public function setReadDate(int $readDate): void
    {
        $this->readDate = $readDate;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     *
     * @return void
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @param string $label
     *
     * @return void
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @param string $url
     *
     * @return void
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getMedia(): string
    {
        return $this->media;
    }

    /**
     * @param string $media
     *
     * @return void
     */
    public function setMedia(string $media): void
    {
        $this->media = $media;
    }

    /**
     * @return int
     */
    public function getSysLanguageUid(): int
    {
        return $this->sysLanguageUid;
    }

    /**
     * @param int $sysLanguageUid
     *
     * @return void
     */
    public function setSysLanguageUid(int $sysLanguageUid): void
    {
        $this->sysLanguageUid = $sysLanguageUid;
        $this->_languageUid = $sysLanguageUid;
    }

    /**
     * @return int
     */
    public function getL10nParent(): int
    {
        return $this->l10nParent;
    }

    /**
     * @param int $l10nParent
     *
     * @return void
     */
    public function setL10nParent(int $l10nParent): void
    {
        $this->l10nParent = $l10nParent;
    }

    /**
     * @return int
     */
    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    /**
     * @param int $tstamp
     *
     * @return void
     */
    public function setTstamp(int $tstamp): void
    {
        $this->tstamp = $tstamp;
    }


}
