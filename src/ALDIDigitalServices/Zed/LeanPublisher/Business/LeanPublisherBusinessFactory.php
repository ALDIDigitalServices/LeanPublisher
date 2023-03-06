<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Business;

use ALDIDigitalServices\Zed\LeanPublisher\Business\Consumer\LeanPublisherEventConsumer;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Consumer\LeanPublisherEventConsumerInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManager;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManagerInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Publish\Publisher;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Publish\PublisherInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Resynchronization\Resynchronization;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization;
use ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherDependencyProvider;
use Pyz\Zed\Event\Business\EventFacadeInterface;
use Pyz\Zed\Store\Business\StoreFacadeInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Zed\Kernel\Business\AbstractBusinessFactory;

/**
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherEntityManagerInterface getEntityManager()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherConfig getConfig()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherRepositoryInterface getRepository()
 */
class LeanPublisherBusinessFactory extends AbstractBusinessFactory
{
    /**
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Business\Consumer\LeanPublisherEventConsumerInterface
     */
    public function createLeanPublisherEventConsumer(): LeanPublisherEventConsumerInterface
    {
        return new LeanPublisherEventConsumer(
            $this->createMessageTransferManager(),
            $this->createEventHandlerPluginResolver(),
            $this->createLeanPublisher(),
            $this->createSynchronization(),
            $this->getStoreFacade(),
        );
    }

    /**
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Business\Resynchronization\Resynchronization
     */
    public function createResynchronization(): Resynchronization
    {
        return new Resynchronization(
            $this->createSynchronization(),
            $this->createEventHandlerPluginResolver(),
            $this->getRepository(),
            $this->getConfig()
        );
    }

    /**
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver
     */
    public function createEventHandlerPluginResolver(): EventHandlerPluginResolver
    {
        return new EventHandlerPluginResolver(
            $this->getEventHandlerPlugins()
        );
    }

    /**
     * @return array
     */
    protected function getEventHandlerPlugins(): array
    {
        return $this->getProvidedDependency(LeanPublisherDependencyProvider::PLUGINS_EVENT_HANDLER);
    }

    /**
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization
     */
    protected function createSynchronization(): Synchronization
    {
        return new Synchronization(
            $this->getSynchronizationPlugins()
        );
    }

    /**
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Synchronization\LeanPublisherSynchronizationPluginInterface[]
     */
    protected function getSynchronizationPlugins(): array
    {
        return $this->getProvidedDependency(LeanPublisherDependencyProvider::PLUGINS_SYNCHRONIZATION);
    }

    /**
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManagerInterface
     */
    protected function createMessageTransferManager(): MessageTransferManagerInterface
    {
        return new MessageTransferManager(
            $this->getEventFacade(),
            $this->getUtilEncodingService()
        );
    }

    /**
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Business\Publish\PublisherInterface
     */
    protected function createLeanPublisher(): PublisherInterface
    {
        return new Publisher(
            $this->getEntityManager()
        );
    }

    /**
     * @return \Pyz\Zed\Event\Business\EventFacadeInterface
     */
    protected function getEventFacade(): EventFacadeInterface
    {
        return $this->getProvidedDependency(LeanPublisherDependencyProvider::FACADE_EVENT);
    }

    /**
     * @return \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface
     */
    protected function getUtilEncodingService(): UtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(LeanPublisherDependencyProvider::SERVICE_UTIL_ENCODING);
    }

    /**
     * @return \Pyz\Zed\Store\Business\StoreFacadeInterface
     */
    protected function getStoreFacade(): StoreFacadeInterface
    {
        return $this->getProvidedDependency(LeanPublisherDependencyProvider::FACADE_STORE);
    }
}
