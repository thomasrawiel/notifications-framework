<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use ApacheSolrForTypo3\Solr\IndexQueue\Initializer\Record;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\WorkspaceRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\MathUtility;

class RecordUtility
{

    /**
     * returns the uid of a record string, e.g. tx_news_domain_model_news_123 => (int)123
     */
    public static function getRecordUidAsIntegerFromConfiguration(Configuration $configuration): int
    {
        $record = $configuration->getRecord();

        return self::getRecordUidAsIntegerFromRecordString($record);
    }

    /**
     * returns the uid of a record string, e.g. tx_news_domain_model_news_123 => (int)123
     */
    public static function getRecordUidAsIntegerFromRecordString(string $recordString): int
    {
        if (MathUtility::canBeInterpretedAsInteger($recordString)) {
            return (int)$recordString;
        }

        return (int)array_pop(GeneralUtility::trimExplode('_', $recordString));
    }

    /**
     * returns the table of a record string, e.g. tx_news_domain_model_news_123 => tx_news_domain_model_news
     */
    public static function getTableFromRecordString(string $recordString): string
    {
        $parts = GeneralUtility::trimExplode('_', $recordString);
        array_pop($parts);

        return implode('_', $parts);
    }

    public static function getRecord(string $recordString): array
    {
        $table = self::getTableFromRecordString($recordString);
        $uid = self::getRecordUidAsIntegerFromRecordString($recordString);

        return BackendUtility::getRecord($table, $uid);
    }

    public static function getRecordTranslations(string $recordString, int $language): false|array
    {
        $table = self::getTableFromRecordString($recordString);
        $uid = self::getRecordUidAsIntegerFromRecordString($recordString);

        $recordLocalization = false;

        if (BackendUtility::isTableLocalizable($table)) {
            $tcaCtrl = $GLOBALS['TCA'][$table]['ctrl'];

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable($table);
            $queryBuilder->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class))
                ->add(GeneralUtility::makeInstance(WorkspaceRestriction::class, static::getBackendUserAuthentication()->workspace));

            $queryBuilder->select('*')
                ->from($table)
                ->where(
                    $queryBuilder->expr()->eq(
                        $tcaCtrl['transOrigPointerField'] ?? $tcaCtrl['l10n_source'],
                        $queryBuilder->createNamedParameter($uid, Connection::PARAM_INT)
                    ),
                    $queryBuilder->expr()->eq(
                        $tcaCtrl['languageField'],
                        $queryBuilder->createNamedParameter((int)$language, Connection::PARAM_INT)
                    )
                )
                ->setMaxResults(1);

            $recordLocalization = reset($queryBuilder->executeQuery()->fetchAllAssociative());
        }

        return $recordLocalization;
    }

    protected static function getBackendUserAuthentication(): ?BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'] ?? null;
    }
}
