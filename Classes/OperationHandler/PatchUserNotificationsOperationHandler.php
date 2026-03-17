<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\OperationHandler;

use Psr\Http\Message\ResponseInterface;
use SourceBroker\T3api\Domain\Model\OperationInterface;
use SourceBroker\T3api\Exception\OperationNotAllowedException;
use SourceBroker\T3api\OperationHandler\AbstractItemOperationHandler;
use Symfony\Component\HttpFoundation\Request;
use TRAW\NotificationsFramework\Domain\Model\Json\Reference;
use TRAW\NotificationsFramework\Domain\Repository\Json\ReferenceRepository;
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

        $repository = GeneralUtility::makeInstance(ReferenceRepository::class);
        $repository->setObjectType(Reference::class);

        //mark all notifications as read
        if ($operation->getKey() === 'patch_user_notifications') {
            $userNotifications = $repository->findByFeUser($feUid);
            foreach ($userNotifications as $userNotification) {
                if ($userNotification->getRead() === 0) {
                    $this->setRead($userNotification, $repository);
                    $persist = true;
                }
            }
            if ($persist ?? false) {
                $persistenceManager->persistAll();
            }
            $result = ['success' => true];
        }
        //mark a specific notification as read
        if ($operation->getKey() === 'patch_user_notification') {
            $object = parent::handle($operation, $request, $route, $response);
            $this->deserializeOperation($operation, $request, $object);
            $this->validationService->validateObject($object);

            if ($object->getRead() === 0) {
                $this->setRead($object, $repository);
                $object->setRead(1);
                $repository->update($object);

                if ($reference->_getProperty('_localizedUid') === $reference->getUid()) {
                    foreach ($repository->findByL10nParent($reference->getUid()) as $translatedReference) {
                        $translatedReference->setRead(1);
                        $repository->update($translatedReference);
                    }
                }

                $persistenceManager->persistAll();
            }

            $result = $object;
        }

        return $result;
    }
}
