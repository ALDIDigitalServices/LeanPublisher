<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication;

use ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherDependencyProvider;
use Spryker\Client\Search\SearchClientInterface;
use Spryker\Client\Storage\StorageClientInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Zed\Kernel\Communication\AbstractCommunicationFactory;

class LeanPublisherCommunicationFactory extends AbstractCommunicationFactory
{
    /**
     * @return array|\ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface[]
     */
    public function getLeanPublisherEventHandlerPlugins(): array
    {
        return $this->getProvidedDependency(LeanPublisherDependencyProvider::PLUGINS_EVENT_HANDLER);
    }

    /**
     * @return \Spryker\Client\Search\SearchClientInterface
     */
    public function getSearchClient(): SearchClientInterface
    {
        return $this->getProvidedDependency(LeanPublisherDependencyProvider::CLIENT_SEARCH);
    }

    /**
     * @return \Spryker\Client\Storage\StorageClientInterface
     */
    public function getStorageClient(): StorageClientInterface
    {
        return $this->getProvidedDependency(LeanPublisherDependencyProvider::CLIENT_STORAGE);
    }

    /**
     * @return \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface
     */
    public function getUtilEncodingService(): UtilEncodingServiceInterface
    {
        return $this->getProvidedDependency(LeanPublisherDependencyProvider::SERVICE_UTIL_ENCODING);
    }
}
