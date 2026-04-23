<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Domain\Model\Notification;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class NotificationRepository extends Repository
{
    public function notificationExists(int $configuration): bool
    {
        $query = $this->createQuery();

        return $query->matching(
                $query->equals('configuration', $configuration),
            )->count() > 0;
    }

    public function getNotification(int $uid): array
    {
        return $this->getNotificationsByDemand(['uid' => $uid]);
    }

    public function getNotificationsByDemand(array $demand): array
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(Configuration::TABLE_NAME);
        $qb->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $qb->select('*')
            ->from(Notification::TABLE_NAME);
        if ($demand['uid'] ?? false && MathUtility::canBeInterpretedAsInteger($demand['uid'])) {
            $qb->where($qb->expr()->eq('uid', $qb->createNamedParameter($demand['uid'])));
            return $qb->execute()->fetchAssociative();
        }

        $sortField = $demand['sortField'] ?? 'uid';
        if (!isset($GLOBALS['TCA'][Notification::TABLE_NAME]['columns'][$sortField])) {
            $sortField = $GLOBALS['TCA'][Notification::TABLE_NAME]['ctrl']['sorting'] ?? 'uid';
        }

        $sortDirection = $demand['sortDirection'] ?? 'ASC';

        $constraints = [
            $qb->expr()->eq('sys_language_uid', $qb->createNamedParameter(0, ParameterType::INTEGER)),
        ];
        if (is_array($demand['pid'])) {
            $constraints[] = $qb->expr()->in('pid', $qb->createNamedParameter($demand['pid'], ArrayParameterType::INTEGER));
        } else {
            $qb->expr()->eq('pid', $qb->createNamedParameter($demand['pid'], ParameterType::INTEGER));
        }

        $qb->where(...$constraints);
        $qb->orderBy($sortField, $sortDirection);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function sortList(array $notiications, string $sortField, string $sortDirection = 'asc'): array
    {
        $sortDirection = strtolower($sortDirection);

        if (!isset($notiications[0][$sortField])) {
            $sortField = 'uid';
        }

        $modifier = $sortDirection === 'asc' ? 1 : -1;
        usort($notiications, function ($a, $b) use ($sortField, $modifier) {
            $aValue = $a[$sortField] ?? null;
            $bValue = $b[$sortField] ?? null;

            if ($aValue === $bValue) {
                return 0;
            }
            if ($aValue === null) return 1;
            if ($bValue === null) return -1;
            return ($aValue < $bValue ? -1 : 1) * $modifier;
        });


        return $notiications;
    }
}
