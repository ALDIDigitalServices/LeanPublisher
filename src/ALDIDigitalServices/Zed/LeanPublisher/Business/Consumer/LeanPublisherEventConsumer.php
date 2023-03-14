<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Consumer;

use ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManagerInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Publish\PublisherInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Trait\LeanPublisherDataCollectionTrait;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherSearchPublishPluginInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherStoragePublishPluginInterface;
use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;
use Generated\Shared\Transfer\LeanPublisherDataTransfer;
use Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer;
use Spryker\Zed\Store\Business\StoreFacadeInterface;

class LeanPublisherEventConsumer implements LeanPublisherEventConsumerInterface
{
    use LeanPublisherDataCollectionTrait;

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
     * @var \Spryker\Zed\Store\Business\StoreFacadeInterface
     */
    protected StoreFacadeInterface $storeFacade;

    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManagerInterface $messageTransferManager
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver $eventHandlerPluginResolver
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Publish\PublisherInterface $leanPublisher
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization $synchronization
     * @param \Spryker\Zed\Store\Business\StoreFacadeInterface $storeFacade
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
     * {@inheritDoc}
     *
     * @param array $queueReceiveMessageTransfers
     *
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
            $leanPublishAndSynchronizationRequest = $this->getData($leanPublisherQueueMessageCollection, $leanPublishAndSynchronizationRequest, $eventHandler);

            $leanPublishAndSynchronizationRequest = $this->addDeleteDataFromQueueMessages($leanPublisherQueueMessageCollection, $leanPublishAndSynchronizationRequest, $eventHandler);

            if ($this->hasNoPublishData($leanPublishAndSynchronizationRequest)) {
                $this->markMessagesAcknowledged($leanPublisherQueueMessageCollection);

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
        return (
                $leanPublishAndSynchronizationRequest->getPublishDataWrite() === null &&
                $leanPublishAndSynchronizationRequest->getPublishDataDelete() === null
            )
            ||
            $leanPublishAndSynchronizationRequest->getPublishDataWrite() !== null &&
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
    protected function getData(
        LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection,
        LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequest,
        LeanPublisherEventHandlerPluginInterface $eventHandler
    ): LeanPublishAndSynchronizationRequestTransfer {
        $data = $this->loadData($leanPublisherQueueMessageCollection, $eventHandler);

        if ($data) {
            $leanPublishAndSynchronizationRequest = $this->mapData($eventHandler, $data, $leanPublishAndSynchronizationRequest);
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
    protected function addDeleteDataFromQueueMessages(
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
            $leanPublishAndSynchronizationRequest = $this->addToDeleteDataCollection(
                $leanPublishAndSynchronizationRequest,
                $this->prepareDeleteMessage($deleteMessage, $storeName)
            );
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
     * @return \Generated\Shared\Transfer\LeanPublisherDataTransfer
     */
    protected function prepareDeleteMessage($deleteMessage, string $storeName): LeanPublisherDataTransfer
    {
        $reference = $this->messageTransferManager
            ->getEventQueueSendMessageBodyTransfer($deleteMessage)
            ->getTransferData()['id'];

        return (new LeanPublisherDataTransfer())
            ->setStore($storeName)
            ->setReference($reference);
    }

    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $eventHandler
     * @param array $loadedData
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    protected function mapData(
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
    protected function loadData(
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
