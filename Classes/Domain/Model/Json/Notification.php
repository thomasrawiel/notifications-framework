<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model\Json;

use SourceBroker\T3api\Annotation as T3api;
use TRAW\NotificationsFramework\Domain\Model\Type;
use TRAW\NotificationsFramework\Events\Data\NotificationJsonDataEvent;
use TRAW\NotificationsFramework\Utility\ImageUtility;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

class Notification extends AbstractEntity
{
    /** @var ObjectStorage<\TRAW\NotificationsFramework\Domain\Model\FileReference>|null */
    protected ?ObjectStorage $image = null;

    public function __construct()
    {
        $this->image = new ObjectStorage();
    }

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * @var string
     */
    protected string $label = '';
    /**
     * @var string
     */
    protected string $message = '';
    /**
     * @var string
     */
    protected string $type = '';
    /**
     * @var string
     */
    protected string $url = '';
    /**
     * @var int
     */
    protected int $configuration = 0;


    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getImage(): ?ObjectStorage
    {
        return $this->image;
    }

    public function getConfiguration(): int
    {
        return $this->configuration;
    }
}
