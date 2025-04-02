<?php
declare(strict_types=1);

namespace TRAW\NotificationsFramework\Events;


use TRAW\NotificationsFramework\Domain\DTO\Settings;
use TYPO3\CMS\Core\Configuration\Features;

/**
 * Class AbstractEventListener
 */
abstract class AbstractEventListener
{

    /**
     * @var string
     */
    protected string $expectedEventClass = AbstractEvent::class;
    
    /**
     * @param AbstractEvent $event
     *
     * @return mixed|void
     */
    public function __invoke(AbstractEvent $event)
    {
        //check if the event has the expected class
        //event class must extend AbstractEvent:class
        //note: using get_class instead of instanceof, because we want to compare with the sub class
        if (is_subclass_of($event, AbstractEvent::class)
            && get_class($event) === $this->expectedEventClass
        ) {
            $this->invokeEventAction($event);
        }
    }

    /**
     * @param AbstractEvent $event
     *
     * @return mixed|void
     */
    protected function invokeEventAction(AbstractEvent $event)
    {
    }
}