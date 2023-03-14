<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Resynchronization;

use ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Resynchronization\Iterator\ResynchronizationIterator;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherSearchPublishPluginInterface;
use ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherConfig;
use ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherRepositoryInterface;
use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;
use Generated\Shared\Transfer\LeanPublisherResynchronizationRequestTransfer;
use Iterator;
use Spryker\Zed\Kernel\Persistence\EntityManager\InstancePoolingTrait;

class Resynchronization
{
    use InstancePoolingTrait;

    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization
     */
    protected Synchronization $synchronization;

    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver
     */
    protected EventHandlerPluginResolver $eventHandlerPluginResolver;

    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherRepositoryInterface
     */
    protected LeanPublisherRepositoryInterface $leanPublisherRepository;

    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherConfig
     */
    protected LeanPublisherConfig $leanPublisherConfig;

    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization $synchronization
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver $eventHandlerPluginResolver
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherRepositoryInterface $leanPublisherRepository
     * @param \ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherConfig $leanPublisherConfig
     */
    public function __construct(
        Synchronization $synchronization,
        EventHandlerPluginResolver $eventHandlerPluginResolver,
        LeanPublisherRepositoryInterface $leanPublisherRepository,
        LeanPublisherConfig $leanPublisherConfig
    ) {
        $this->synchronization = $synchronization;
        $this->eventHandlerPluginResolver = $eventHandlerPluginResolver;
        $this->leanPublisherRepository = $leanPublisherRepository;
        $this->leanPublisherConfig = $leanPublisherConfig;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherResynchronizationRequestTransfer $leanPublisherResynchronizationRequestTransfer
     *
     * @return void
     */
    public function resynchronizeData(LeanPublisherResynchronizationRequestTransfer $leanPublisherResynchronizationRequestTransfer): void
    {
        $requestedEventHandlers = $this->eventHandlerPluginResolver->getEventHandlerPluginsByResynchronizationRequest($leanPublisherResynchronizationRequestTransfer);

        $isPoolingStateChanged = $this->disableInstancePooling();

        foreach ($requestedEventHandlers as $eventHandlerPlugin) {
            $this->exportData($leanPublisherResynchronizationRequestTransfer, $eventHandlerPlugin);
        }

        if ($isPoolingStateChanged) {
            $this->enableInstancePooling();
        }
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherResynchronizationRequestTransfer $leanPublisherResynchronizationRequestTransfer
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $eventHandlerPlugin
     *
     * @return void
     */
    protected function exportData(
        LeanPublisherResynchronizationRequestTransfer $leanPublisherResynchronizationRequestTransfer,
        LeanPublisherEventHandlerPluginInterface $eventHandlerPlugin
    ): void {
        foreach ($this->createResynchronizationIterator($leanPublisherResynchronizationRequestTransfer->getIds(), $eventHandlerPlugin) as $entities) {
            $publisherSynchronizationRequest = $this->buildLeanPublisherSynchronizationRequest($entities, $eventHandlerPlugin);
            $this->synchronization->synchronizeData($publisherSynchronizationRequest, $eventHandlerPlugin);
        }
    }

    /**
     * @param array $ids
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $eventHandlerPlugin
     *
     * @return \Iterator
     */
    protected function createResynchronizationIterator(array $ids, LeanPublisherEventHandlerPluginInterface $eventHandlerPlugin): Iterator
    {
        return new ResynchronizationIterator($eventHandlerPlugin, $this->leanPublisherRepository, $ids, $this->leanPublisherConfig->getLeanPublisherResynchronizationChunkSize());
    }

    /**
     * @param array $entities
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $eventHandlerPlugin
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    protected function buildLeanPublisherSynchronizationRequest(
        array $entities,
        LeanPublisherEventHandlerPluginInterface $eventHandlerPlugin
    ): LeanPublishAndSynchronizationRequestTransfer {
        $leanPublisherSynchronizationRequestTransfer = new LeanPublishAndSynchronizationRequestTransfer();
        $leanPublisherSynchronizationRequestTransfer->setSyncDataWrite($entities);

        if ($eventHandlerPlugin instanceof LeanPublisherSearchPublishPluginInterface) {
            $leanPublisherSynchronizationRequestTransfer->setElasticSearchIndex($eventHandlerPlugin->getElasticSearchIndex());
        }

        return $leanPublisherSynchronizationRequestTransfer;
    }
}
