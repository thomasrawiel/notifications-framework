<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

abstract class AbstractDemandRepository extends Repository
{
    public function getByDemand(string $demandTable, array $demand = []): array
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($demandTable);
        $qb->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $qb->select('*')->from($demandTable);

        if ($demand['uid'] ?? false && MathUtility::canBeInterpretedAsInteger($demand['uid'])) {
            $qb->where($qb->expr()->eq('uid', $qb->createNamedParameter($demand['uid'], ParameterType::INTEGER)));
            return $qb->execute()->fetchAssociative();
        }

        if ($demand['l10n_parent'] ?? false) {
            $qb->where($qb->expr()->eq('l10n_parent', $qb->createNamedParameter($demand['l10n_parent'], ParameterType::INTEGER)));
            return $qb->execute()->fetchAllAssociative();
        }

        $sortField = $demand['sortField'] ?? 'uid';
        if (!isset($GLOBALS['TCA'][$demandTable]['columns'][$sortField])) {
            $sortField = $GLOBALS['TCA'][$demandTable]['ctrl']['sorting'] ?? 'uid';
        }

        $sortDirection = $demand['sortDirection'] ?? 'ASC';

        $constraints = [];
        $constraints[] = $qb->expr()->eq('sys_language_uid', $qb->createNamedParameter(0, ParameterType::INTEGER));

        if ($demand !== []) {
            $pid = $demand['pid'] ?? null;
            if ($pid !== null && $pid !== '' && $pid !== []) {
                if (is_array($pid)) {
                    $constraints[] = $qb->expr()->in('pid', $qb->createNamedParameter($pid, ArrayParameterType::INTEGER));
                } else {
                    $constraints[] = $qb->expr()->eq('pid', $qb->createNamedParameter($pid, ParameterType::INTEGER));
                }
            }

            $restOfDemand = array_diff_key(
                $demand,
                array_flip(['uid', 'pid', 'l10n_parent', 'sortDirection', 'sortField', 'maxitems', 'perPage', 'currentPage', 'filter'])
            );
            if ($restOfDemand !== []) {
                foreach ($restOfDemand as $key => $value) {
                    $constraints[] = $qb->expr()->eq($key, $qb->createNamedParameter($value, ParameterType::STRING));
                }
            }

            if ($demand['maxitems'] ?? false) {
                $qb->setMaxResults((int)$demand['maxitems']);
            }
        }

        $qb->where(...$constraints);
        $qb->orderBy($sortField, $sortDirection);

        return $qb->executeQuery()->fetchAllAssociative();
    }
}
