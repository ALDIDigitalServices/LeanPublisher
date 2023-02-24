<?php

namespace ALDIDigitalServices\Zed\LeanPublisher;

use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Synchronization\SearchSynchronizationPlugin;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Synchronization\StorageSynchronizationPlugin;
use Pyz\Zed\Event\Business\EventFacadeInterface;
use Pyz\Zed\Store\Business\StoreFacadeInterface;
use Spryker\Client\Search\SearchClientInterface;
use Spryker\Client\Storage\StorageClientInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

class LeanPublisherDependencyProvider extends AbstractBundleDependencyProvider
{
    public const FACADE_EVENT = 'FACADE_EVENT';
    public const FACADE_STORE = 'FACADE_STORE';

    public const CLIENT_SEARCH = 'CLIENT_SEARCH';
    public const CLIENT_STORAGE = 'CLIENT_STORAGE';

    public const SERVICE_UTIL_ENCODING = 'SERVICE_UTIL_ENCODING';

    public const PLUGINS_EVENT_HANDLER = 'PLUGINS_EVENT_HANDLER';
    public const PLUGINS_SYNCHRONIZATION = 'PLUGINS_SYNCHRONIZATION';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = $this->addEventFacade($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addEventHandlerPlugins($container);
        $container = $this->addSynchronizationPlugins($container);
        $container = $this->addStoreFacade($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideCommunicationLayerDependencies(Container $container): Container
    {
        $container = $this->addEventHandlerPlugins($container);
        $container = $this->addUtilEncodingService($container);
        $container = $this->addSearchClient($container);
        $container = $this->addStorageClient($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addEventHandlerPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_EVENT_HANDLER, function (): array {
            return $this->getEventHandlerPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addSynchronizationPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_SYNCHRONIZATION, function (): array {
            return $this->getSynchronizationPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addEventFacade(Container $container): Container
    {
        $container->set(static::FACADE_EVENT, static function (Container $container): EventFacadeInterface {
            return $container->getLocator()->event()->facade();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addStoreFacade(Container $container): Container
    {
        $container->set(static::FACADE_STORE, static function (Container $container): StoreFacadeInterface {
            return $container->getLocator()->store()->facade();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addSearchClient(Container $container): Container
    {
        $container->set(static::CLIENT_SEARCH, static function (Container $container): SearchClientInterface {
            return $container->getLocator()->search()->client();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addStorageClient(Container $container): Container
    {
        $container->set(static::CLIENT_STORAGE, static function (Container $container): StorageClientInterface {
            return $container->getLocator()->storage()->client();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addUtilEncodingService(Container $container): Container
    {
        $container->set(static::SERVICE_UTIL_ENCODING, static function (Container $container): UtilEncodingServiceInterface {
            return $container->getLocator()->utilEncoding()->service();
        });

        return $container;
    }

    /**
     * @return array
     */
    protected function getEventHandlerPlugins(): array
    {
        return [];
    }

    /**
     * @return array
     */
    protected function getSynchronizationPlugins(): array
    {
        return [
            new SearchSynchronizationPlugin(),
            new StorageSynchronizationPlugin(),
        ];
    }
}
