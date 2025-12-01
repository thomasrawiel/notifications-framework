<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\OperationHandler;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\OperationHandler\AbstractItemOperationHandler;
use SourceBroker\T3api\Security\OperationAccessChecker;
use SourceBroker\T3api\Serializer\ContextBuilder\DeserializationContextBuilder;
use SourceBroker\T3api\Service\SerializerService;
use SourceBroker\T3api\Service\ValidationService;
use Symfony\Component\HttpFoundation\Request;
use TRAW\NotificationsFramework\Domain\Repository\NotificationRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\DomainObject\AbstractDomainObject;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

class PatchUserNotificationsOperationHandler extends AbstractItemOperationHandler
{
    /**
     * @param OperationInterface $operation
     * @param Request            $request
     *
     * @return bool
     */
    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return $operation->getKey() === 'patch_user_notification'
            || $operation->getKey() === 'patch_user_notifications';
    }

    /** @noinspection ReferencingObjectsInspection */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response): AbstractDomainObject|array
    {
        $feUid = $request->attributes->get('frontend.user')->user['uid'] ?? null;
        if (empty($feUid)) {
            throw new OperationNotAllowedException($operation, 1726218688);
        }
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        $result = null;

        //mark all notifications as read
        if ($operation->getKey() === 'patch_user_notifications') {
            $notificationRepository = GeneralUtility::makeInstance(NotificationRepository::class);
            $userNotifications = $notificationRepository->findByFeUser($feUid);
            foreach ($userNotifications as $userNotification) {
                if ($userNotification->getRead() === 0) {
                    $userNotification->setRead(1);
                    $userNotification->setReadDate(time());
                    $notificationRepository->update($userNotification);
                }
            }
            $persistenceManager->persistAll();
            $result =  ['success' => true];
        }
        //mark a specific notification as read
        if ($operation->getKey() === 'patch_user_notification') {
            $repository = $this->getRepositoryForOperation($operation);
            $object = parent::handle($operation, $request, $route, $response);
            $this->deserializeOperation($operation, $request, $object);
            $this->validationService->validateObject($object);
            $repository->update($object);
            $persistenceManager->persistAll();

            $result = $object;
        }

        return $result;
    }


}
