<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Event\Subscriber;

use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Event\Listeners\LeanPublisherEventListener;
use Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer;
use Spryker\Zed\Event\Dependency\EventCollectionInterface;
use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Event\Dependency\Plugin\EventSubscriberInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Communication\LeanPublisherCommunicationFactory getFactory()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherConfig getConfig()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Business\LeanPublisherFacadeInterface getFacade()
 */
class LeanPublisherEventSubscriber extends AbstractPlugin implements EventSubscriberInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return \Spryker\Zed\Event\Dependency\EventCollectionInterface
     */
    public function getSubscribedEvents(EventCollectionInterface $eventCollection): EventCollectionInterface
    {
        foreach ($this->getFactory()->getLeanPublisherEventHandlerPlugins() as $eventHandlerPlugin) {
            $eventCollection = $this->registerEvents(
                $eventCollection,
                $eventHandlerPlugin->getSubscribedEventCollection(),
                $eventHandlerPlugin->getQueueName()
            );
        }

        return $eventCollection;
    }

    /**
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     * @param \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer $eventCollectionTransfer
     * @param string $eventQueueName
     *
     * @return \Spryker\Zed\Event\Dependency\EventCollectionInterface
     */
    protected function registerEvents(
        EventCollectionInterface $eventCollection,
        LeanPublisherEventCollectionTransfer $eventCollectionTransfer,
        string $eventQueueName
    ): EventCollectionInterface {
        foreach ($eventCollectionTransfer->getEvents() as $eventTransfer) {
            $this->addListener(
                $eventTransfer->getEventName(),
                $eventQueueName,
                $eventCollection
            );
        }

        return $eventCollection;
    }

    /**
     * @param string $eventName
     * @param string $eventQueueName
     * @param \Spryker\Zed\Event\Dependency\EventCollectionInterface $eventCollection
     *
     * @return \Spryker\Zed\Event\Dependency\EventCollectionInterface
     */
    protected function addListener(string $eventName, string $eventQueueName, EventCollectionInterface $eventCollection): EventCollectionInterface
    {
        $eventCollection->addListenerQueued(
            $eventName,
            $this->createLeanPublisherEventListener(),
            0,
            null,
            $eventQueueName
        );

        return $eventCollection;
    }

    /**
     * @return \Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface
     */
    protected function createLeanPublisherEventListener(): EventBulkHandlerInterface
    {
        return new LeanPublisherEventListener();
    }
}
