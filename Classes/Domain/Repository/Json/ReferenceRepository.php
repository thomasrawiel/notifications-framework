<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository\Json;

use SourceBroker\T3api\Domain\Repository\CommonRepository;
use Symfony\Component\HttpFoundation\Request;
use TRAW\NotificationsFramework\Domain\Model\FrontendUser;
use TRAW\NotificationsFramework\Domain\Repository\FrontendUserRepository;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

class ReferenceRepository extends CommonRepository
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
        $query->setQuerySettings($querySettings);

        $constraints = [
            $query->equals('fe_user', $request->attributes->get('frontend.user')->user['uid']),
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
        return $query;
    }

    public function findByFeUser(int $feuserUid)
    {
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
