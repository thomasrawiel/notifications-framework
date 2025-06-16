<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Configuration\Configuration;
use SourceBroker\T3api\Domain\Model\CollectionOperation;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\OperationHandler\AbstractCollectionOperationHandler;
use SourceBroker\T3api\OperationHandler\CollectionGetOperationHandler;
use SourceBroker\T3api\Response\AbstractCollectionResponse;
use Symfony\Component\HttpFoundation\Request;
use TRAW\NotificationsFramework\Domain\Model\Json\Notification;
use TRAW\NotificationsFramework\Domain\Repository\Json\NotificationRepository;
use TRAW\NotificationsFramework\Events\Configuration\RepositoryClassesEvent;
use TRAW\NotificationsFramework\Events\Data\FilteredNotificationsFetchedEvent;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class GetUserNotificationsOperationHandler extends CollectionGetOperationHandler
{
    /**
     * @param OperationInterface $operation
     * @param Request            $request
     *
     * @return bool
     */
    public static function supports(OperationInterface $operation, Request $request): bool
    {
        return $operation->getKey() === 'get_user_notifications';
    }

    /** @noinspection ReferencingObjectsInspection */
    public function handle(OperationInterface $operation, Request $request, array $route, ?ResponseInterface &$response)
    {
        $feUid = $request->attributes->get('frontend.user')->user['uid'] ?? null;
        if (empty($feUid)) {
            throw new OperationNotAllowedException($operation, 1726218688);
        }

        /** @var CollectionOperation $operation */
        AbstractCollectionOperationHandler::handle($operation, $request, $route, $response);
        $collectionResponseClass = Configuration::getCollectionResponseClass();

        $repositoryClassesEvent = $this->eventDispatcher->dispatch(
            new RepositoryClassesEvent(NotificationRepository::class, Notification::class),
        );

        /** @var NotificationRepository $repository */
        $repository = GeneralUtility::makeInstance($repositoryClassesEvent->getRepositoryClass());
        $repository->setObjectType($repositoryClassesEvent->getObjectClass());

        if (!is_subclass_of($collectionResponseClass, AbstractCollectionResponse::class)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Collection response class (`%s`) has to be an instance of `%s`',
                    $collectionResponseClass,
                    AbstractCollectionResponse::class
                )
            );
        }

        $event = new FilteredNotificationsFetchedEvent($repository->findFiltered($operation->getFilters(), $request));
        $userNotifications = $this->eventDispatcher->dispatch($event)->getQuery();

        /** @var AbstractCollectionResponse $responseObject */
        $responseObject = GeneralUtility::makeInstance(
            $collectionResponseClass,
            $operation,
            $request,
            $userNotifications
        );

        return $responseObject;
    }
}