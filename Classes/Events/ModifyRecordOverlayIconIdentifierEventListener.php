<?php

declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events;

use TRAW\NotificationsFramework\Domain\Model\Configuration;
use TRAW\NotificationsFramework\Utility\RecordIconOverlayUtility;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Imaging\Event\ModifyRecordOverlayIconIdentifierEvent;

/**
 * Class ModifyRecordOverlayIconIdentifierEventListener
 */
#[AsEventListener(
    identifier: 'traw-notifications-framework/modify-record-overlay-icon-identifier',
)]
final readonly class ModifyRecordOverlayIconIdentifierEventListener
{
    /**
     * @param ModifyRecordOverlayIconIdentifierEvent $event
     *
     * @return void
     */
    public function __invoke(ModifyRecordOverlayIconIdentifierEvent $event): void
    {
        if ($event->getTable() === Configuration::TABLE_NAME) {
            $identifier = RecordIconOverlayUtility::getRecordIconOverlay($event->getRecord());

            if (!empty($identifier)) {
                $event->setOverlayIconIdentifier('my-overlay-icon-identifier');
            }
        }
    }
}