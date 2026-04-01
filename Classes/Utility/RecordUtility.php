<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use ApacheSolrForTypo3\Solr\IndexQueue\Initializer\Record;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
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
    public static function getRecordUidAsIntegerFromRecordString(string $recordString): int {
        if (MathUtility::canBeInterpretedAsInteger($record)) {
            return (int)$record;
        }

        return (int)array_pop(GeneralUtility::trimExplode('_', $recordString));
    }
    /**
     * returns the table of a record string, e.g. tx_news_domain_model_news_123 => tx_news_domain_model_news
     */
    public static function getTableFromRecordString(string $recordString) : string {
        $parts = GeneralUtility::trimExplode('_', $recordString);
        array_pop($parts);

        return implode('_', $parts);
    }
}
