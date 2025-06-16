<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository\Json;

use SourceBroker\T3api\Domain\Repository\CommonRepository;
use Symfony\Component\HttpFoundation\Request;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * Class NotificationRepository
 */
class NotificationRepository extends CommonRepository
{
    /**
     * @param array   $apiFilters
     * @param Request $request
     *
     * @return QueryInterface
     */
    public function findFiltered(array $apiFilters, Request $request): QueryInterface
    {
        $query = parent::findFiltered($apiFilters, $request);

        $query->setQuerySettings($query->getQuerySettings()->setRespectStoragePage(false));

        $constraint = $query->equals('feUser', $request->attributes->get('frontend.user')->user['uid']);
        if ($query->getConstraint()) {
            $query->matching(
                $query->logicalAnd(
                    $constraint,
                    $query->getConstraint()
                )
            );
        } else {
            $query->matching($constraint);
        }

        return $query;
    }
}
