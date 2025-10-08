<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model\Json;

use SourceBroker\T3api\Annotation as T3api;
use TRAW\NotificationsFramework\Events\Data\NotificationJsonDataEvent;
use TRAW\NotificationsFramework\Utility\ImageUtility;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Resource\ProcessedFile;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * @var T3api\|null  <-- this line prevents PhpStorm from removing the alias
 */

/**
 * @T3api\ApiResource(
 *     collectionOperations={
 *          "get_user_notifications"={
 *              "method"="GET",
 *              "path"="/users/notifications",
 *              "security"="frontend.user.isLoggedIn",
 *              "normalizationContext"={
 *                  "groups"={"api_get_collection_notificationsframework_json_users_notifications"}
 *              },
 *          },
 *     },
 *     itemOperations={
 *          "patch_user_notification"={
 *                 "method"="PATCH",
 *                 "path"="/users/notifications/read/{id}",
 *                 "security"="frontend.user.isLoggedIn",
 *                  "normalizationContext"={
 *                   "groups"={"api_patch_item_notificationsframework_json_users_notifications"}
 *                  },
 *          },
 *               "patch_user_notifications"={
 *                  "method"="PATCH",
 *                  "path"="/users/notifications/read-all",
 *                  "security"="frontend.user.isLoggedIn",
 *                   "normalizationContext"={
 *                    "groups"={"api_patch_item_notificationsframework_json_users_notifications"}
 *                   },
 *           },
 *     }
 * )
 */
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
     * @var int
     */
    protected int $tstamp = 0;

    /**
     * @var int
     * @T3api\Serializer\Groups({
     *      "api_patch_item_notificationsframework_json_users_notifications"
     *  })
     */
    protected int $read = 0;

    /**
     * @var int
     */
    protected int $readDate = 0;

    /**
     * @var string
     */
    protected string $type = '';

    protected string $url = '';

    protected int $feUser = 0;
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
     * @return int
     */
    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    /**
     * @return int
     */
    public function getRead(): int
    {
        return $this->read;
    }

    /**
     * @return int
     */
    public function getReadDate(): int
    {
        return $this->readDate;
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

    /**
     * @param int $read
     *
     * @return void
     */
    public function setRead(int $read): void
    {
        if ($this->read === 0) {
            $this->read = 1;
            $this->readDate = time();
        }
    }


    /**
     * @return array
     */
    private function getEventConfiguration(): array
    {
        return [
            'configuration' => $this->configuration,
            'user' => $this->feUser,
            'read' => $this->read,
        ];
    }

    /**
     * @return array
     * @T3api\Serializer\VirtualProperty()
     * @T3api\Serializer\Groups({
     *     "api_get_collection_notificationsframework_json_users_notifications"
     * })
     */
    public function getNotificationData(): array
    {
        $dispatcher = GeneralUtility::makeInstance(EventDispatcher::class);
        $tstampIso = new \DateTime();
        $tstampIso->setTimestamp($this->tstamp);

        $data = [
            'title' => $this->label,
            'text' => $this->message,
            'timestamp' => $tstampIso,
            'isUnread' => !$this->read,
            'type' => $this->type,
            'url' => $this->url,
            'media' => null,
        ];
        if($this->image instanceof ObjectStorage && $this->image->count()) {
            $processedImage = ImageUtility::getProcessedImage($this->image->getArray()[0] ?? []);
            if($processedImage instanceof ProcessedFile) {
                $data['media'] = PathUtility::getAbsoluteWebPath($processedImage->getPublicUrl());
            }
        }

        return $dispatcher->dispatch(new NotificationJsonDataEvent($data, $this->getEventConfiguration()))->getData();
    }
}
