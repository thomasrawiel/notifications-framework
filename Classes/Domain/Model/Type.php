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

    public function isValidType(?string $type = null): bool
    {
        if (is_null($type)) {
            return false;
        }

        return $this->isRecordType($type) || $this->isCustomMessageType($type);
    }

    public function isRecordType(?string $type = null): bool
    {
        return in_array($type, $this->getTypesWithRecordField());
    }

    public function isCustomMessageType(?string $type = null): bool
    {
        return in_array($type, $this->getTypesWithCustomMessage());
    }
}
