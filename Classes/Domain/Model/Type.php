<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Domain\Model;

use TRAW\NotificationsFramework\Events\Configuration\TypesWithCustomMessageEvent;
use TRAW\NotificationsFramework\Events\Configuration\TypesWithRecordFieldEvent;
use TYPO3\CMS\Core\EventDispatcher\EventDispatcher;

/**
 * Class Type
 */
final class Type
{
    /**
     *
     */
    const DEFAULT = 'default';
    /**
     *
     */
    const INFO = 'info';
    /**
     *
     */
    const SUCCESS = 'success';
    /**
     *
     */
    const WARNING = 'warning';
    /**
     *
     */
    const ERROR = 'error';
    /**
     *
     */
    const RECORDADDED = 'recordadded';
    /**
     *
     */
    const RECORDUPDATED = 'recordupdated';

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(private readonly EventDispatcher $eventDispatcher)
    {
    }

    /**
     * @return string
     */
    public function getTypesWithCustomMessage(): TypesWithCustomMessageEvent
    {
        return $this->eventDispatcher->dispatch(new TypesWithCustomMessageEvent(
            [
                '',
                self::DEFAULT,
                SELF::INFO,
                SELF::SUCCESS,
                SELF::WARNING,
                self::ERROR,
            ]
        ));
    }

    /**
     * @return string
     */
    public function getTypesWithRecordField(): TypesWithRecordFieldEvent
    {
        return $this->eventDispatcher->dispatch(new TypesWithRecordFieldEvent(
            [
                self::RECORDADDED,
                self::RECORDUPDATED,
            ]
        ));
    }

    /**
     * @return string
     */
    public function getTypesWithCustomMessageList(): string
    {
        return $this->getTypesWithCustomMessage()->__toString();
    }

    /**
     * @return string
     */
    public function getTypesWithRecordFieldList(): string
    {
        return $this->getTypesWithRecordField()->__toString();
    }
}
