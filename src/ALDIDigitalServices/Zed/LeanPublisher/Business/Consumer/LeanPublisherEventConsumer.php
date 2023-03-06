<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Consumer;

use ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManagerInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Publish\PublisherInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherSearchPublishPluginInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherStoragePublishPluginInterface;
use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;
use Generated\Shared\Transfer\LeanPublisherDataTransfer;
use Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer;
use Pyz\Zed\Store\Business\StoreFacadeInterface;

class LeanPublisherEventConsumer implements LeanPublisherEventConsumerInterface
{
    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManagerInterface
     */
    protected MessageTransferManagerInterface $messageTransferManager;

    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver
     */
    protected EventHandlerPluginResolver $eventHandlerPluginResolver;

    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Business\Publish\PublisherInterface
     */
    protected PublisherInterface $leanPublisher;

    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization
     */
    protected Synchronization $synchronization;

    /**
     * @var \Pyz\Zed\Store\Business\StoreFacadeInterface
     */
    protected StoreFacadeInterface $storeFacade;

    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManagerInterface $messageTransferManager
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver $eventHandlerPluginResolver
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Publish\PublisherInterface $leanPublisher
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization $synchronization
     * @param \Pyz\Zed\Store\Business\StoreFacadeInterface $storeFacade
     */
    public function __construct(
        MessageTransferManagerInterface $messageTransferManager,
        EventHandlerPluginResolver $eventHandlerPluginResolver,
        PublisherInterface $leanPublisher,
        Synchronization $synchronization,
        StoreFacadeInterface $storeFacade
    ) {
        $this->messageTransferManager = $messageTransferManager;
        $this->eventHandlerPluginResolver = $eventHandlerPluginResolver;
        $this->leanPublisher = $leanPublisher;
        $this->synchronization = $synchronization;
        $this->storeFacade = $storeFacade;
    }

    /**
     * {inheritDoc}
     *
     * @param array $queueReceiveMessageTransfers
     *
     * @throws \Exception
     * @return array
     */
    public function processLeanPublisherMessages(array $queueReceiveMessageTransfers): array
    {
        $queueMessagesGroupedByQueueName = $this->messageTransferManager->groupQueueMessageTransfersByQueueName($queueReceiveMessageTransfers);

        foreach ($queueMessagesGroupedByQueueName as $queueName => $queueMessages) {
            $eventHandler = $this->eventHandlerPluginResolver->getEventHandlerPluginFromQueueName($queueName);

            $leanPublisherQueueMessageCollection = $this->messageTransferManager
                ->validateAndFilterQueueMessages(
                    $queueMessages,
                    $eventHandler->getSubscribedEventCollection()
                );

            $leanPublishAndSynchronizationRequest = (new LeanPublishAndSynchronizationRequestTransfer())->setQueryClass($eventHandler->getPublishTableQueryClass());

            $leanPublisherQueueMessageCollection = $this->messageTransferManager->setWriteAndDeleteMessages($leanPublisherQueueMessageCollection);
            $leanPublishAndSynchronizationRequest = $this->addWriteData($leanPublisherQueueMessageCollection, $leanPublishAndSynchronizationRequest, $eventHandler);
            $leanPublishAndSynchronizationRequest = $this->addDeleteData($leanPublisherQueueMessageCollection, $leanPublishAndSynchronizationRequest, $eventHandler);

            if ($this->hasNoPublishData($leanPublishAndSynchronizationRequest)) {
                continue;
            }

            $leanPublishAndSynchronizationRequest = $this->leanPublisher->publishData($leanPublishAndSynchronizationRequest);

            $this->synchronization->synchronizeData($leanPublishAndSynchronizationRequest, $eventHandler);

            $this->markMessagesAcknowledged($leanPublisherQueueMessageCollection);
        }

        return $leanPublisherQueueMessageCollection->getValidatedMessages()->getArrayCopy() +
            $leanPublisherQueueMessageCollection->getInvalidMessages()->getArrayCopy();
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection
     *
     * @return void
     */
    protected function markMessagesAcknowledged(LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection): void
    {
        $this->messageTransferManager->markMessagesAcknowledged($leanPublisherQueueMessageCollection);
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequest
     *
     * @return bool
     */
    protected function hasNoPublishData(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequest): bool
    {
        return $leanPublishAndSynchronizationRequest->getPublishDataWrite() !== null &&
            $leanPublishAndSynchronizationRequest->getPublishDataDelete() !== null &&
            empty($leanPublishAndSynchronizationRequest->getPublishDataWrite()->getData()) &&
            empty($leanPublishAndSynchronizationRequest->getPublishDataDelete()->getData());
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequest
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $eventHandler
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    protected function addWriteData(
        LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection,
        LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequest,
        LeanPublisherEventHandlerPluginInterface $eventHandler
    ): LeanPublishAndSynchronizationRequestTransfer {
        $writeData = $this->loadWriteData($leanPublisherQueueMessageCollection, $eventHandler);

        if ($writeData) {
            $leanPublishAndSynchronizationRequest = $this->mapWriteData($eventHandler, $writeData, $leanPublishAndSynchronizationRequest);
        }

        return $leanPublishAndSynchronizationRequest;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequest
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $eventHandler
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    protected function addDeleteData(
        LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection,
        LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequest,
        LeanPublisherEventHandlerPluginInterface $eventHandler
    ): LeanPublishAndSynchronizationRequestTransfer {
        $deleteMessages = $leanPublisherQueueMessageCollection->getDeleteMessages();

        if (!$deleteMessages->count()) {
            return $leanPublishAndSynchronizationRequest;
        }

        $storeName = $this->storeFacade->getCurrentStore()->getName();

        foreach ($deleteMessages as $deleteMessage) {
            $deleteMessage = $this->prepareDeleteMessage($deleteMessage, $storeName);
            $leanPublishAndSynchronizationRequest->addPublishDeleteData($deleteMessage);
        }

        if ($eventHandler instanceof LeanPublisherSearchPublishPluginInterface) {
            $leanPublishAndSynchronizationRequest->setElasticSearchIndex($eventHandler->getElasticSearchIndex());
        }

        return $leanPublishAndSynchronizationRequest;
    }

    /**
     * @param $deleteMessage
     * @param string $storeName
     *
     * @return array
     */
    protected function prepareDeleteMessage($deleteMessage, string $storeName): array
    {
        $originId = $this->messageTransferManager
            ->getEventQueueSentMessageBodyTransfer($deleteMessage)
            ->getTransferData()['id'];

        return (new LeanPublisherDataTransfer())
            ->setStore($storeName)
            ->setIdOrigin($originId)
            ->modifiedToArray();
    }

    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $eventHandler
     * @param array $loadedData
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    protected function mapWriteData(
        LeanPublisherEventHandlerPluginInterface $eventHandler,
        array $loadedData,
        LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
    ): LeanPublishAndSynchronizationRequestTransfer {
        if ($eventHandler instanceof LeanPublisherSearchPublishPluginInterface) {
            $leanPublishAndSynchronizationRequestTransfer = $eventHandler->mapDataForSearch($loadedData, $leanPublishAndSynchronizationRequestTransfer);
            $leanPublishAndSynchronizationRequestTransfer->setElasticSearchIndex($eventHandler->getElasticSearchIndex());
        }

        if ($eventHandler instanceof LeanPublisherStoragePublishPluginInterface) {
            $leanPublishAndSynchronizationRequestTransfer = $eventHandler->mapDataForStorage($loadedData, $leanPublishAndSynchronizationRequestTransfer);
        }

        return $leanPublishAndSynchronizationRequestTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $eventHandler
     *
     * @return array
     */
    private function loadWriteData(
        LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection,
        LeanPublisherEventHandlerPluginInterface $eventHandler
    ): array {
        $writeData = [];
        if ($leanPublisherQueueMessageCollection->getWriteMessages()->count()) {
            $writeMessages = $this->messageTransferManager->groupEventTransfersByTable($leanPublisherQueueMessageCollection->getWriteMessages());
            $writeData = $eventHandler->loadData($writeMessages);
        }

        return $writeData;
    }
}
