<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Message;

use ArrayObject;
use Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer;

interface MessageTransferManagerInterface
{
    /**
     * @param array $queueReceiveMessageTransfers
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection
     *
     * @return \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer
     */
    public function validateQueueMessages(
        array $queueReceiveMessageTransfers,
        LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection
    ): LeanPublisherQueueMessageCollectionTransfer;

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection
     *
     * @param array $eventEntityFilterCriteria
     *
     * @return \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer
     */
    public function filterQueueMessageTransfers(
        LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection,
        array $eventEntityFilterCriteria
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
     * @param \ArrayObject $queueMessages
     *
     * @return void
     */
    public function markMessagesAcknowledged(ArrayObject $queueMessages): void;
}
