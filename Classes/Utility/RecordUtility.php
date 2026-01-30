<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Utility;

use ApacheSolrForTypo3\Solr\IndexQueue\Initializer\Record;
use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TYPO3\CMS\Core\Utility\MathUtility;

class RecordUtility
{

    /**
     * returns the uid of a record string, e.g. tx_news_domain_model_news_123 => (int)123
     *
     *
     * @param int|string $record
     *
     * @return int
     */
    public static function getRecordUidAsIntegerFromConfiguration(Configuration $configuration): int
    {
        $record = $configuration->getRecord();

        if (MathUtility::canBeInterpretedAsInteger($record)) {
            return (int)$record;
        }

        return (int)substr($record, strlen($configuration->getTable()) + 1);
    }
}
