<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model\Json;

use SourceBroker\T3api\Annotation as T3Api;
use TRAW\NotificationsFramework\Events\Data\NotificationDataEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
 * )
 */

/**
 * @var T3Api\|null  <-- this line prevents PhpStorm from removing the alias
 */
class Notification extends \TRAW\NotificationsFramework\Domain\Model\Notification
{
    /**
     * @var int
     */
    protected int $tstamp = 0;


    /**
     * @var string
     * @T3api\Serializer\Groups({
     *     "api_get_collection_notificationsframework_json_users_notifications"
     * })
     */
    protected string $title = '';

    /**
     * @var int
     */
    protected int $configuration = 0;


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

        return $dispatcher->dispatch(new NotificationDataEvent([
            'text' => $this->getNotificationText(),
            'date' => $this->getNotificationDate(),
            'read' => $this->read,
        ], $this->getEventConfiguration()))->getData();
    }
}