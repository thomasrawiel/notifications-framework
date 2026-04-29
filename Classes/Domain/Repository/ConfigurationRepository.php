<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Repository;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\ParameterType;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class ConfigurationRepository extends Repository
{
    public function findAll()
    {
        $query = $this->createQuery();
        $query->setQuerySettings(
            $query->getQuerySettings()->setRespectStoragePage(false),

        );

        $targetAudiences = array_merge(Configuration::AUDIENCE, Configuration::PLACEHOLDER_AUDIENCES);
        $query->matching(
            $query->logicalAnd(
                $query->equals('push', 1),
                $query->equals('done', 0),
                $query->in('target_audience', $targetAudiences)
            )
        );
        return $query->execute();
    }

    public function getTranslations(int $configurationUid)
    {
        $query = $this->createQuery();
        $query->setQuerySettings(
            $query->getQuerySettings()->setRespectStoragePage(false),
            $query->getQuerySettings()->setRespectSysLanguage(false),

        );
        $query->matching(
            $query->logicalAnd(
                $query->greaterThan('sys_language_uid', 0),
                $query->equals('l10n_parent', $configurationUid),
            )
        );
        return $query->execute();
    }


    public function getConfiguration(int $configurationUid): array
    {
        return $this->getConfigurationsByDemand(['uid' => $configurationUid]);
    }

    public function getConfigurationsByDemand(array $demand): array
    {
        $qb = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable(Configuration::TABLE_NAME);
        $qb->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));

        $qb->select('*')
            ->from(Configuration::TABLE_NAME);
        if ($demand['uid'] ?? false && MathUtility::canBeInterpretedAsInteger($demand['uid'])) {
            $qb->where($qb->expr()->eq('uid', $qb->createNamedParameter($demand['uid'], ParameterType::INTEGER)));
            return $qb->execute()->fetchAssociative();
        }

        if ($demand['l10n_parent'] ?? false) {
            $qb->where($qb->expr()->eq('l10n_parent', $qb->createNamedParameter($demand['l10n_parent'], ParameterType::INTEGER)));
            return $qb->execute()->fetchAllAssociative();
        }

        $sortField = $demand['sortField'] ?? 'uid';
        if (!isset($GLOBALS['TCA'][Configuration::TABLE_NAME]['columns'][$sortField])) {
            $sortField = $GLOBALS['TCA'][Configuration::TABLE_NAME]['ctrl']['sorting'] ?? 'uid';
        }

        $sortDirection = $demand['sortDirection'] ?? 'ASC';

        $constraints = [];
        $constraints[] = $qb->expr()->eq('sys_language_uid', $qb->createNamedParameter(0, ParameterType::INTEGER));


        if (is_array($demand['pid'])) {
            $constraints[] = $qb->expr()->in('pid', $qb->createNamedParameter($demand['pid'], ArrayParameterType::INTEGER));
        } else {
            $constraints[] = $qb->expr()->eq('pid', $qb->createNamedParameter($demand['pid'], ParameterType::INTEGER));
        }

        $qb->where(...$constraints);
        $qb->orderBy($sortField, $sortDirection);

        return $qb->executeQuery()->fetchAllAssociative();
    }
}
