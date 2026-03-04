<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model\Json;

use SourceBroker\T3api\Annotation as T3api;
use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Model\Json\Notification;
use TRAW\NotificationsFramework\Domain\Model\Type;
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
 *             "method"="PATCH",
 *             "path"="/users/notification/read/{id}",
 *             "security"="frontend.user.isLoggedIn",
 *              "normalizationContext"={
 *                 "groups"={"api_patch_item_notificationsframework_json_users_notification"}
 *              },
 *          },
 *          "patch_user_notifications"={
 *              "method"="PATCH",
 *              "path"="/users/notification/read-all",
 *              "security"="frontend.user.isLoggedIn",
 *              "normalizationContext"={
 *                 "groups"={"api_patch_item_notificationsframework_json_users_notifications"}
 *               },
 *           },
 *     }
 * )
 */
class Reference extends AbstractEntity
{
    /**
     * @var int
     */
    protected int $feUser;
    /**
     * @var \TRAW\NotificationsFramework\Domain\Model\Json\Notification
     */
    protected Notification $notification;

    /**
     * @var int
     * @T3api\Serializer\Groups({
     *      "api_patch_item_notificationsframework_json_users_notifications"
     *  })
     */
    protected int $read = 0;

    /**
     * @var int
     * @T3api\Serializer\Groups({
     *      "api_patch_item_notificationsframework_json_users_notifications"
     *  })
     */
    protected int $readDate = 0;
    /**
     * @var int
     */
    protected int $tstamp = 0;

    public function getFeUser(): int
    {
        return $this->feUser;
    }

    public function getNotification(): \TRAW\NotificationsFramework\Domain\Model\Json\Notification
    {
        return $this->notification;
    }

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
        $this->readDate = $read === 1 ? ($this->readDate ? : time()) : 0;
    }

    public function getReadDate(): int
    {
        return $this->readDate;
    }

    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    private function getEventConfiguration(): array
    {
        return [
            'configuration' => $this->notification?->getConfiguration(),
            'user' => $this->user,
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
            'title' => $this->notification->getLabel(),
            'text' => $this->notification->getMessage(),
            'timestamp' => $tstampIso,
            'isUnread' => !$this->read,
            'type' => match ($this->notification->getType()) {
                Type::DEFAULT,
                Type::INFO,
                Type::SUCCESS,
                Type::WARNING,
                Type::ERROR => 'message',
                Type::RECORDADDED,
                Type::RECORDUPDATED => 'record',
                Type::USEREVENT => 'custom',
                default => $this->notification->getType(),
            },
            'url' => $this->notification->getUrl(),
            'media' => null,
        ];
        if ($this->notification->getImage() instanceof ObjectStorage && $this->notification->getImage()->count()) {
            $imageUtility = GeneralUtility::makeInstance(ImageUtility::class);
            $processedImage = $imageUtility->getProcessedImage($this->notification->getImage()->getArray()[0] ?? []);
            if ($processedImage instanceof ProcessedFile) {
                $data['media'] = PathUtility::getAbsoluteWebPath($processedImage->getPublicUrl());
            }
        }

        return $dispatcher->dispatch(new NotificationJsonDataEvent($data, $this->getEventConfiguration()))->getData();
    }
}
