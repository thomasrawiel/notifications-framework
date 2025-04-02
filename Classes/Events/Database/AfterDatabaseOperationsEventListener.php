<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events\Database;

use TRAW\NotificationsFramework\Events\AbstractEventListener;
use TRAW\NotificationsFramework\Events\AbstractEvent;
use TRAW\NotificationsFramework\Events\Database\AfterDatabaseOperationsEvent;

/**
 * Class AfterDatabaseOperationsEventListener
 */
class AfterDatabaseOperationsEventListener extends AbstractEventListener
{
    /**
     * @var string
     */
    protected string $expectedEventClass = AfterDatabaseOperationsEvent::class;

    protected function invokeEventAction(AbstractEvent $event)
    {
       
    }
}