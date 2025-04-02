<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events;

use TRAW\NotificationsFramework\Domain\Model\BackendUserInfo;

/**
 * Class AbstractEvent
 */
abstract class AbstractEvent
{
    /**
     * @var string
     */
    protected string $type = 'abstract';
    /**
     * @var BackendUserInfo
     */
    private BackendUserInfo $backendUser;

    /**
     * BackendLoginEvent constructor.
     *
     * @param BackendUserInfo $backendUser
     */
    public function __construct(BackendUserInfo $backendUser)
    {
        $this->backendUser = $backendUser;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return BackendUserInfo
     */
    public function getBackendUser(): BackendUserInfo
    {
        return $this->backendUser;
    }
}