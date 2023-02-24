<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Message;

use ArrayObject;
use Generated\Shared\Transfer\EventQueueSendMessageBodyTransfer;
use Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer;
use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Pyz\Zed\Event\Business\EventFacadeInterface;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Shared\Event\EventConfig as SharedEventConfig;
use Spryker\Zed\Event\Business\Exception\MessageTypeNotFoundException;

class MessageTransferManager implements MessageTransferManagerInterface
{
    /**
     * @var \Pyz\Zed\Event\Business\EventFacadeInterface
     */
    protected EventFacadeInterface $eventFacade;

    /**
     * @var \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface
     */
    protected UtilEncodingServiceInterface $utilEncodingService;

    /**
     * @param \Pyz\Zed\Event\Business\EventFacadeInterface $eventFacade
     * @param \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(
        EventFacadeInterface $eventFacade,
        UtilEncodingServiceInterface $utilEncodingService
    ) {
        $this->eventFacade = $eventFacade;
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param array $queueReceiveMessageTransfers
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection
     *
     * @throws \Spryker\Zed\Event\Business\Exception\MessageTypeNotFoundException
     * @return \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer
     */
    public function validateQueueMessages(
        array $queueReceiveMessageTransfers,
        LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection
    ): LeanPublisherQueueMessageCollectionTransfer {
        foreach ($queueReceiveMessageTransfers as $queueReceiveMessageTransfer) {
            $eventQueueSentMessageBodyTransfer = $this->getEventQueueSentMessageBodyTransfer($queueReceiveMessageTransfer);

            if (!$this->isMessageBodyValid($eventQueueSentMessageBodyTransfer)) {
                $queueReceiveMessageTransfer = $this->markMessageAsFailed(
                    $queueReceiveMessageTransfer,
                    'Message body is not valid'
                );
                $leanPublisherQueueMessageCollection->addInvalidMessage($queueReceiveMessageTransfer);
                continue;
            }

            $leanPublisherQueueMessageCollection->addValidMessage($queueReceiveMessageTransfer);
        }

        return $leanPublisherQueueMessageCollection;
    }

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
    ): LeanPublisherQueueMessageCollectionTransfer {
        $eventEntityFilterCriteria = $this->formatFilterCriteria($eventEntityFilterCriteria);

        $validMessages = $leanPublisherQueueMessageCollection->getValidMessages();
        $messagesToKeep = new ArrayObject();
        foreach ($validMessages as $queueReceiveMessageTransfer) {
            $eventQueueSentMessageBodyTransfer = $this->getEventQueueSentMessageBodyTransfer($queueReceiveMessageTransfer);
            $eventName = $eventQueueSentMessageBodyTransfer->getEventName();
            $modifiedColumns = $eventQueueSentMessageBodyTransfer->getTransferData()['modified_columns'];

            if (empty($eventEntityFilterCriteria)) {
                // no filter mapping, everything is valid
                $messagesToKeep->append($queueReceiveMessageTransfer);
                continue;
            }

            if (isset($eventEntityFilterCriteria[$eventName])) {
                // if there is no property mapping, event is valid
                if (empty($eventEntityFilterCriteria[$eventName])) {
                    $messagesToKeep->append($queueReceiveMessageTransfer);
                    continue;
                }

                // event is valid if changed property is in mapping, invalid if not
                foreach ($eventEntityFilterCriteria[$eventName] as $property) {
                    if (in_array($property, $modifiedColumns, true)) {
                        $messagesToKeep->append($queueReceiveMessageTransfer);
                    } else {
                        $leanPublisherQueueMessageCollection->addInvalidMessage($queueReceiveMessageTransfer);
                    }
                }
            }
        }

        return $leanPublisherQueueMessageCollection
            ->setValidMessages($messagesToKeep);
    }

    /**
     * @param array $queueReceiveMessageTransfers
     *
     * @return array
     */
    public function groupQueueMessageTransfersByQueueName(array $queueReceiveMessageTransfers): array
    {
        $groupedQueueReceiveMessageTransfers = [];
        foreach ($queueReceiveMessageTransfers as $queueReceiveMessageTransfer) {
            $groupedQueueReceiveMessageTransfers[$queueReceiveMessageTransfer->getQueueName()][] = $queueReceiveMessageTransfer;
        }

        return $groupedQueueReceiveMessageTransfers;
    }


    /**
     * @param \ArrayObject $eventEntityTransfers
     *
     * @return array
     */
    public function groupEventTransfersByTable(ArrayObject $eventEntityTransfers): array
    {
        $eventsGroupedByTable = [];
        foreach ($eventEntityTransfers as $eventEntityTransfer) {
            $eventQueueSentMessageBodyTransfer = $this->getEventQueueSentMessageBodyTransfer($eventEntityTransfer);
            $tableName = $eventQueueSentMessageBodyTransfer->getTransferData()['name'];
            $eventsGroupedByTable[$tableName][] = $eventEntityTransfer;
        }

        return $eventsGroupedByTable;
    }

    /**
     * @param array $filterCriteria
     *
     * @return array
     */
    protected function formatFilterCriteria(array $filterCriteria): array
    {
        $formattedFilterCriteria = [];
        foreach ($filterCriteria as $key => $value) {
            if (!is_array($value)) {
                $formattedFilterCriteria[$value] = [];
                continue;
            }
            $formattedFilterCriteria[$key] = $value;
        }

        return $formattedFilterCriteria;
    }


    /**
     * @param \Generated\Shared\Transfer\EventQueueSendMessageBodyTransfer $eventQueueSendMessageBodyTransfer
     *
     * @return bool
     */
    protected function isMessageBodyValid(EventQueueSendMessageBodyTransfer $eventQueueSendMessageBodyTransfer): bool
    {
        if (!$eventQueueSendMessageBodyTransfer->getListenerClassName()) {
            $this->logConsumerAction('Listener class name is not set.');

            return false;
        }

        if (!$eventQueueSendMessageBodyTransfer->getTransferClassName()) {
            $this->logConsumerAction('Transfer class name is not set.');

            return false;
        }

        if (!class_exists($eventQueueSendMessageBodyTransfer->getListenerClassName())) {
            $this->logConsumerAction(
                sprintf(
                    'Listener class "%s" not found.',
                    $eventQueueSendMessageBodyTransfer->getListenerClassName(),
                ),
            );

            return false;
        }

        if (!class_exists($eventQueueSendMessageBodyTransfer->getTransferClassName())) {
            $this->logConsumerAction(
                sprintf(
                    'Transfer class "%s" not found.',
                    $eventQueueSendMessageBodyTransfer->getTransferClassName(),
                ),
            );

            return false;
        }

        return true;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueMessageTransfer
     * @param string $errorMessage
     *
     * @throws \Spryker\Zed\Event\Business\Exception\MessageTypeNotFoundException
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer
     */
    protected function markMessageAsFailed(QueueReceiveMessageTransfer $queueMessageTransfer, string $errorMessage = ''): QueueReceiveMessageTransfer
    {
        if ($queueMessageTransfer->getRoutingKey()) {
            return $queueMessageTransfer;
        }

        $this->setMessage($queueMessageTransfer, 'errorMessage', $errorMessage);
        $queueMessageTransfer->setAcknowledge(false);
        $queueMessageTransfer->setReject(true);
        $queueMessageTransfer->setHasError(true);
        $queueMessageTransfer->setRoutingKey(SharedEventConfig::EVENT_ROUTING_KEY_ERROR);

        return $queueMessageTransfer;
    }


    /**
     * @param \ArrayObject $queueMessages
     *
     * @return void
     */
    public function markMessagesAcknowledged(ArrayObject $queueMessages): void
    {
        array_map(static function ($data) {
            $data->setAcknowledge(true);

            return $data;
        }, $queueMessages->getArrayCopy());
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueMessageTransfer
     * @param string $messageType
     * @param string $message
     *
     * @throws \Spryker\Zed\Event\Business\Exception\MessageTypeNotFoundException
     *
     * @return void
     */
    protected function setMessage(QueueReceiveMessageTransfer $queueMessageTransfer, string $messageType, string $message = ''): void
    {
        if (!$messageType) {
            throw new MessageTypeNotFoundException('Message type is not defined');
        }

        $queueMessageBody = $this->utilEncodingService->decodeJson($queueMessageTransfer->getQueueMessage()->getBody(), true);
        $queueMessageBody[$messageType] = $message;
        $queueMessageTransfer->getQueueMessage()->setBody($this->utilEncodingService->encodeJson($queueMessageBody));
    }


    /**
     * @param string $message
     *
     * @return void
     */
    protected function logConsumerAction(string $message): void
    {
        $this->eventFacade->logEventMessage('[async] ' . $message);
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return \Generated\Shared\Transfer\EventQueueSendMessageBodyTransfer
     */
    protected function getEventQueueSentMessageBodyTransfer(QueueReceiveMessageTransfer $queueReceiveMessageTransfer): EventQueueSendMessageBodyTransfer
    {
        return $this->createEventQueueSentMessageBodyTransfer(
            $queueReceiveMessageTransfer->getQueueMessage()->getBody(),
        );
    }

    /**
     * @param string $body
     *
     * @return \Generated\Shared\Transfer\EventQueueSendMessageBodyTransfer
     */
    protected function createEventQueueSentMessageBodyTransfer(string $body): EventQueueSendMessageBodyTransfer
    {
        $eventQueueSentMessageBodyTransfer = new EventQueueSendMessageBodyTransfer();
        $eventQueueSentMessageBodyTransfer->fromArray(
            $this->utilEncodingService->decodeJson($body, true),
            true,
        );

        return $eventQueueSentMessageBodyTransfer;
    }
}
