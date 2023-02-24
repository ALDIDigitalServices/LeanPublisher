<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver;

use ALDIDigitalServices\Zed\LeanPublisher\Business\Exception\EventHandlerNotFoundException;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;

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
     * @throws \Exception
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
}
