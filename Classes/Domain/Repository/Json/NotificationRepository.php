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
        $querySettings = $query->getQuerySettings()
            ->setRespectStoragePage(false);
            //->setRespectSysLanguage(false);
        $query->setQuerySettings($querySettings);

        $language = $request->attributes->get('language');
        $constraints = [
            $query->equals('feUser', $request->attributes->get('frontend.user')->user['uid']),
            $query->logicalOr(
                $query->equals('sys_language_uid', 0),
                $query->equals('sys_language_uid', $language->getLanguageId),
            )
        ];

        if ($query->getConstraint()) {
            $query->matching(
                $query->logicalAnd(
                    $query->getConstraint(),
                    ...$constraints
                )
            );
        } else {
            $query->matching(
                $query->logicalAnd(
                    ...$constraints
                )
            );
        }

        $orignalQuery = $query;


        return $query;
    }
}
