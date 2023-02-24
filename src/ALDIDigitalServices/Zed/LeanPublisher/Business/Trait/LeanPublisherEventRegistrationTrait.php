<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Trait;

use Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer;
use Generated\Shared\Transfer\LeanPublisherEventTransfer;

trait LeanPublisherEventRegistrationTrait
{
    protected static ?LeanPublisherEventCollectionTransfer $eventCollectionTransfer = null;

    /**
     * @return \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer
     */
    protected function getEventCollection(): LeanPublisherEventCollectionTransfer
    {
        $eventFilterCollection = static::$eventCollectionTransfer;
        static::$eventCollectionTransfer = null;

        return $eventFilterCollection;
    }

    /**
     * @param string $eventName
     * @param array $properties
     *
     * @return \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer
     */
    protected function registerForEvent(string $eventName, array $properties = []): LeanPublisherEventCollectionTransfer
    {
        $eventCollectionTransfer = $this->getEventCollectionTransfer();
        $this->buildEventFilterCollectionTransfer($eventCollectionTransfer, $eventName, $properties);

        return $eventCollectionTransfer;
    }

    /**
     * @return \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer
     */
    protected function getEventCollectionTransfer(): LeanPublisherEventCollectionTransfer
    {
        if (static::$eventCollectionTransfer === null) {
            static::$eventCollectionTransfer = new LeanPublisherEventCollectionTransfer();
        }

        return static::$eventCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer $eventCollectionTransfer
     * @param string $eventName
     * @param array $eventPropertyMapping
     *
     * @return void
     */
    protected function buildEventFilterCollectionTransfer(LeanPublisherEventCollectionTransfer $eventCollectionTransfer, string $eventName, array $eventPropertyMapping = []): void
    {
        $leanPublisherEvent = new LeanPublisherEventTransfer();
        $leanPublisherEvent->setEventName($eventName);

        foreach ($eventPropertyMapping as $property) {
            $leanPublisherEvent->addFilterProperty($property);
        }

        $eventCollectionTransfer->addEvent($leanPublisherEvent);
    }
}
