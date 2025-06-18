<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model\Json;

use SourceBroker\T3api\Annotation as T3api;
use TRAW\NotificationsFramework\Events\Data\NotificationJsonDataEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

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

    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_notificationsframework_json_users_notifications"
     * })
     */
    protected string $title = '';

    protected int $tstamp = 0;

    protected int $readDate = 0;

    /**
     * @var int
     * @T3api\Serializer\Groups({
     *      "api_patch_item_notificationsframework_json_users_notifications"
     *  })
     */
    protected int $read = 0;

    public function getTstamp(): int
    {
        return $this->tstamp;
    }

    public function getRead(): int
    {
        return $this->read;
    }

    public function setRead(int $read): void
    {
        if($this->read === 0) {
            $this->read = 1;
            $this->readDate = time();
        }
    }




    public function getTitle(): string
    {
        if (empty($this->title)) {
            return "Dummy title";
        }
        return $this->title;
    }


    /**
     * @return \DateTime
     */
    private function getNotificationDate(): \DateTime
    {
        $d = new \DateTime();
        $d->setTimestamp($this->tstamp);
        return $d;
    }


    /**
     * @return string
     */
    private function getNotificationText(): string
    {
        return 'hello darkness my old friend!';
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
return [
    'title' => $this->getTitle(),
    'text' => $this->getNotificationText(),
    'timestamp' => $this->getNotificationDate(),
    'isUnread' => !$this->read,
    'url' => null,
    'media' => null,
];
       // return $dispatcher->dispatch(new NotificationJsonDataEvent(, $this->getEventConfiguration()))->getData();
    }
}