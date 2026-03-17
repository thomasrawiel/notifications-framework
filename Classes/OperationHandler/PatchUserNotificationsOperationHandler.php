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

        $repository = GeneralUtility::makeInstance(ReferenceRepository::class);
        $repository->setObjectType(Reference::class);
        $result = [];
        $persist = false;
        //mark all notifications as read
        if ($operation->getKey() === 'patch_user_notifications') {
            $notificationReferences = $repository->findByFeUser($feUid);
            foreach ($notificationReferences as $reference) {
                if ($reference->getRead() === 0) {
                    $this->setRead($reference, $repository);
                    $persist = true;
                }
            }

            $result = ['success' => true];
        }
        //mark a specific notification as read
        if ($operation->getKey() === 'patch_user_notification') {
            $object = parent::handle($operation, $request, $route, $response);
            $this->deserializeOperation($operation, $request, $object);
            $this->validationService->validateObject($object);

            if ($object->getFeUser() !== $feUid) {
                $result = ['success' => false];
                return $result;
            }

            if ($object->getRead() === 0) {
                $this->setRead($object, $repository);
                $persist = true;
            }

            $result = $object;
        }

        if ($persist ?? false) {
            $persistenceManager->persistAll();
        }

        return $result;
    }

    private function setRead(Reference &$reference, ReferenceRepository &$repository): void
    {
        $reference->setRead(1);
        $repository->update($reference);

        if ($reference->_getProperty('_localizedUid') === $reference->getUid()) {
            foreach ($repository->findByL10nParent($reference->getUid()) as $translatedNotification) {
                $translatedNotification->setRead(1);
                $repository->update($translatedNotification);
            }
        }
    }
}
