<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Message;

use ArrayObject;
use Generated\Shared\Transfer\EventQueueSendMessageBodyTransfer;
use Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer;
use Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer;
use Generated\Shared\Transfer\QueueReceiveMessageTransfer;

interface MessageTransferManagerInterface
{
    /**
     * @param array $queueMessageTransfers
     * @param \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer $leanPublisherEventCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer
     */
    public function validateAndFilterQueueMessages(
        array $queueMessageTransfers,
        LeanPublisherEventCollectionTransfer $leanPublisherEventCollectionTransfer
    ): LeanPublisherQueueMessageCollectionTransfer;

    /**
     * @param array $queueReceiveMessageTransfers
     *
     * @return array
     */
    public function groupQueueMessageTransfersByQueueName(array $queueReceiveMessageTransfers): array;

    /**
     * @param \ArrayObject $eventEntityTransfers
     *
     * @return array
     */
    public function groupEventTransfersByTable(ArrayObject $eventEntityTransfers): array;

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $queueMessageCollectionTransfer
     *
     * @return void
     */
    public function markMessagesAcknowledged(LeanPublisherQueueMessageCollectionTransfer $queueMessageCollectionTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return \Generated\Shared\Transfer\EventQueueSendMessageBodyTransfer
     */
    public function getEventQueueSentMessageBodyTransfer(QueueReceiveMessageTransfer $queueReceiveMessageTransfer): EventQueueSendMessageBodyTransfer;
}
