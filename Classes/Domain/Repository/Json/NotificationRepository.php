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

        $query->setOrderings(['tstamp' => 'DESC']);

        $orignalQuery = $query;


        return $query;
    }

    public function findByFeUser(int $feuserUid) {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false);

        $query->matching(
            $query->equals('fe_user', $feuserUid),
        );

        return $query->execute();
    }

    public function findByL10nParent(int $uid)
    {
        $query = $this->createQuery();
        $query->getQuerySettings()->setRespectStoragePage(false)->setRespectSysLanguage(false);

        $query->matching(
            $query->equals('l10n_parent', $uid),
        );

        return $query->execute();
    }
}
