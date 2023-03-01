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
use Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer;

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
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManagerInterface $messageTransferManager
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Resolver\EventHandlerPluginResolver $eventHandlerPluginResolver
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Publish\PublisherInterface $leanPublisher
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization\Synchronization $synchronization
     */
    public function __construct(
        MessageTransferManagerInterface $messageTransferManager,
        EventHandlerPluginResolver $eventHandlerPluginResolver,
        PublisherInterface $leanPublisher,
        Synchronization $synchronization
    ) {
        $this->messageTransferManager = $messageTransferManager;
        $this->eventHandlerPluginResolver = $eventHandlerPluginResolver;
        $this->leanPublisher = $leanPublisher;
        $this->synchronization = $synchronization;
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

        $leanPublisherQueueMessageCollection = new LeanPublisherQueueMessageCollectionTransfer();

        foreach ($queueMessagesGroupedByQueueName as $queueName => $queueMessages) {
            $eventHandler = $this->eventHandlerPluginResolver->getEventHandlerPluginFromQueueName($queueName);

            $leanPublishAndSynchronizationRequestTransfer = (new LeanPublishAndSynchronizationRequestTransfer())
                ->setQueryClass($eventHandler->getPublishTableQueryClass());

            $leanPublisherQueueMessageCollection = $this->messageTransferManager
                ->validateQueueMessages($queueMessages, $leanPublisherQueueMessageCollection);

            $leanPublisherQueueMessageCollection = $this->messageTransferManager
                ->filterQueueMessageTransfers(
                    $leanPublisherQueueMessageCollection,
                    $eventHandler->getSubscribedEventCollection(),
                );

            $data = [];
            if ($leanPublisherQueueMessageCollection->getValidMessages()->count()) {
                $groupedValidMessageTransfers = $this->messageTransferManager
                    ->groupEventTransfersByTable($leanPublisherQueueMessageCollection->getValidMessages());

                $data = $eventHandler->loadData($groupedValidMessageTransfers);
            }


            if (empty($data)) {
                $this->messageTransferManager->markMessagesAcknowledged($leanPublisherQueueMessageCollection->getValidMessages());
                $this->messageTransferManager->markMessagesAcknowledged($leanPublisherQueueMessageCollection->getInvalidMessages());
                continue;
            }

            $leanPublishAndSynchronizationRequestTransfer = $this->mapWriteAndDeleteData($eventHandler, $data, $leanPublishAndSynchronizationRequestTransfer);
            $leanPublishAndSynchronizationRequestTransfer = $this->leanPublisher->publishData($leanPublishAndSynchronizationRequestTransfer);

            $this->synchronization->synchronizeData($leanPublishAndSynchronizationRequestTransfer, $eventHandler);

            $this->messageTransferManager->markMessagesAcknowledged($leanPublisherQueueMessageCollection->getValidMessages());
        }

        return $leanPublisherQueueMessageCollection->getValidMessages()->getArrayCopy() +
            $leanPublisherQueueMessageCollection->getInvalidMessages()->getArrayCopy();
    }

    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $eventHandler
     * @param array $loadedData
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    protected function mapWriteAndDeleteData(
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
}
