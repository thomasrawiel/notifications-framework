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
     *
     */
    const USEREVENT = 'userevent';

    /**
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(private readonly EventDispatcher $eventDispatcher)
    {
    }

    /**
     * @return string
     */
    public function getTypesWithCustomMessage(): array
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
        ))->getTypes();
    }

    /**
     * @return string
     */
    public function getTypesWithRecordField(): array
    {
        return $this->eventDispatcher->dispatch(new TypesWithRecordFieldEvent(
            [
                self::RECORDADDED,
                self::RECORDUPDATED,
                self::USEREVENT,
            ]
        ))->getTypes();
    }

    /**
     * @return string
     */
    public function getTypesWithCustomMessageList(): string
    {
        return implode(',', $this->getTypesWithCustomMessage());
    }

    /**
     * @return string
     */
    public function getTypesWithRecordFieldList(): string
    {
        return implode(',', $this->getTypesWithRecordField());
    }

    public static function isValidType(?string $type = null): bool
    {
        return match ($type) {
            self::DEFAULT,
            self::SUCCESS,
            self::ERROR,
            self::INFO,
            self::WARNING,
            self::USEREVENT,
            self::RECORDADDED,
            self::RECORDUPDATED => true,
            default => false,
        };
    }

    public static function isRecordType(string $type): bool
    {
        return self::isValidType($type)
            && match ($type) {
                self::USEREVENT,
                self::RECORDADDED,
                self::RECORDUPDATED => true,
                default => false,
            };
    }
}
