<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver;

use ALDIDigitalServices\Zed\LeanPublisher\Business\Exception\EventHandlerNotFoundException;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use Generated\Shared\Transfer\LeanPublisherResynchronizationRequestTransfer;

class EventHandlerPluginResolver
{
    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface[]
     */
    protected array $eventHandlerPlugins;

    /**
     * @param array $eventHandlerPlugins
     */
    public function __construct(array $eventHandlerPlugins)
    {
        $this->eventHandlerPlugins = $eventHandlerPlugins;
    }

    /**
     * @param string $queueName
     *
     * @throws \ALDIDigitalServices\Zed\LeanPublisher\Business\Exception\EventHandlerNotFoundException
     *
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface
     */
    public function getEventHandlerPluginFromQueueName(string $queueName): LeanPublisherEventHandlerPluginInterface
    {
        foreach ($this->eventHandlerPlugins as $key => $plugin) {
            if ($key === $queueName) {
                return $plugin;
            }
        }

        throw new EventHandlerNotFoundException(sprintf('EventHandlerPlugin for queue name \'%s\' not found', $queueName));
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherResynchronizationRequestTransfer $leanPublisherReSyncRequestTransfer
     *
     * @throws \ALDIDigitalServices\Zed\LeanPublisher\Business\Exception\EventHandlerNotFoundException
     *
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface[]
     */
    public function getEventHandlerPluginsByResynchronizationRequest(
        LeanPublisherResynchronizationRequestTransfer $leanPublisherReSyncRequestTransfer
    ): array {
        if (empty($leanPublisherReSyncRequestTransfer->getResources())) {
            return $this->eventHandlerPlugins;
        }

        $handlersForRequestedResynchronizationResources = [];
        foreach ($leanPublisherReSyncRequestTransfer->getResources() as $resource) {
            foreach ($this->eventHandlerPlugins as $eventHandlerPlugin) {
                if ($eventHandlerPlugin->getResourceName() === $resource) {
                    $handlersForRequestedResynchronizationResources[] = $eventHandlerPlugin;
                }
            }
        }
        if (empty($handlersForRequestedResynchronizationResources)) {
            throw new EventHandlerNotFoundException(sprintf('No EventHandlerPlugins found for any of the requested resources: %s', implode(', ', $leanPublisherReSyncRequestTransfer->getResources())));
        }

        return $handlersForRequestedResynchronizationResources;
    }

    /**
     * @return array
     */
    public function getAvailableResourceNames(): array
    {
        $resourceNames = [];
        foreach ($this->eventHandlerPlugins as $eventHandlerPlugin) {
            $resourceNames[] = $eventHandlerPlugin->getResourceName();
        }

        sort($resourceNames);

        return array_unique($resourceNames);
    }
}
