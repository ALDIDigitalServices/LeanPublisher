<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Message;

use ALDIDigitalServices\Zed\LeanPublisher\Business\Exception\MissingTableNameException;
use ArrayObject;
use Generated\Shared\Transfer\EventQueueSendMessageBodyTransfer;
use Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer;
use Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer;
use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Spryker\Service\UtilEncoding\UtilEncodingServiceInterface;
use Spryker\Shared\Event\EventConfig as SharedEventConfig;
use Spryker\Shared\Log\LoggerTrait;
use Spryker\Zed\Event\Business\Exception\MessageTypeNotFoundException;

class MessageTransferManager implements MessageTransferManagerInterface
{
    use LoggerTrait;

    /**
     * @var string
     */
    protected const EVENT_TYPE_DELETE = 'delete';

    /**
     * @var array
     */
    protected const EVENT_TYPES_WRITE = ['create', 'update', 'publish'];

    /**
     * @var \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface
     */
    protected UtilEncodingServiceInterface $utilEncodingService;

    /**
     * @param \Spryker\Service\UtilEncoding\UtilEncodingServiceInterface $utilEncodingService
     */
    public function __construct(UtilEncodingServiceInterface $utilEncodingService)
    {
        $this->utilEncodingService = $utilEncodingService;
    }

    /**
     * @param array $queueMessageTransfers
     * @param \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer $leanPublisherEventCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer
     */
    public function validateAndFilterQueueMessages(
        array $queueMessageTransfers,
        LeanPublisherEventCollectionTransfer $leanPublisherEventCollectionTransfer
    ): LeanPublisherQueueMessageCollectionTransfer {
        $leanPublisherQueueMessageCollection = $this->validateQueueMessages($queueMessageTransfers);

        return $this->filterQueueMessageTransfers($leanPublisherQueueMessageCollection, $leanPublisherEventCollectionTransfer);
    }

    /**
     * @param array $queueReceiveMessageTransfers
     *
     * @return \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer
     */
    protected function validateQueueMessages(array $queueReceiveMessageTransfers): LeanPublisherQueueMessageCollectionTransfer
    {
        $leanPublisherQueueMessageCollection = new LeanPublisherQueueMessageCollectionTransfer();

        foreach ($queueReceiveMessageTransfers as $queueReceiveMessageTransfer) {
            $eventQueueSentMessageBodyTransfer = $this->getEventQueueSendMessageBodyTransfer($queueReceiveMessageTransfer);

            if (!$this->isMessageBodyValid($eventQueueSentMessageBodyTransfer)) {
                $queueReceiveMessageTransfer = $this->markMessageAsFailed(
                    $queueReceiveMessageTransfer,
                    'Message body is not valid'
                );
                $leanPublisherQueueMessageCollection->addInvalidMessage($queueReceiveMessageTransfer);

                continue;
            }

            $leanPublisherQueueMessageCollection->addValidatedMessage($queueReceiveMessageTransfer);
        }

        return $leanPublisherQueueMessageCollection;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection
     * @param \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer $leanPublisherEventCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer
     */
    protected function filterQueueMessageTransfers(
        LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollection,
        LeanPublisherEventCollectionTransfer $leanPublisherEventCollectionTransfer
    ): LeanPublisherQueueMessageCollectionTransfer {
        $eventEntityFilterCriteria = $this->formatFilterCriteria($leanPublisherEventCollectionTransfer);

        $validatedMessages = $leanPublisherQueueMessageCollection->getValidatedMessages();
        $messagesToKeep = new ArrayObject();
        foreach ($validatedMessages as $queueReceiveMessageTransfer) {
            $eventQueueSentMessageBodyTransfer = $this->getEventQueueSendMessageBodyTransfer($queueReceiveMessageTransfer);
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

        return $leanPublisherQueueMessageCollection->setValidatedMessages($messagesToKeep);
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer
     */
    public function setWriteAndDeleteMessages(
        LeanPublisherQueueMessageCollectionTransfer $leanPublisherQueueMessageCollectionTransfer
    ): LeanPublisherQueueMessageCollectionTransfer {
        foreach ($leanPublisherQueueMessageCollectionTransfer->getValidatedMessages() as $message) {
            $messageBodyTransfer = $this->getEventQueueSendMessageBodyTransfer($message);

            $eventType = $this->getEventTypeFromEventName($messageBodyTransfer->getEventName());

            if ($eventType === static::EVENT_TYPE_DELETE) {
                $leanPublisherQueueMessageCollectionTransfer->addDeleteMessage($message);
            }

            if (in_array($eventType, static::EVENT_TYPES_WRITE, true)) {
                $leanPublisherQueueMessageCollectionTransfer->addWriteMessage($message);
            }
        }

        return $leanPublisherQueueMessageCollectionTransfer;
    }

    /**
     * @param string $eventName
     *
     * @return string
     */
    protected function getEventTypeFromEventName(string $eventName): string
    {
        $explodedString = explode('.', $eventName);

        return end($explodedString);
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
            $tableName = $this->resolveTableNameFromMessageBody($eventEntityTransfer);
            $eventsGroupedByTable[$tableName][] = $eventEntityTransfer;
        }

        return $eventsGroupedByTable;
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @throws \ALDIDigitalServices\Zed\LeanPublisher\Business\Exception\MissingTableNameException
     *
     * @return string
     */
    protected function resolveTableNameFromMessageBody(QueueReceiveMessageTransfer $queueReceiveMessageTransfer): string
    {
        $eventQueueSendMessageBodyTransfer = $this->getEventQueueSendMessageBodyTransfer($queueReceiveMessageTransfer);

        if (isset($eventQueueSendMessageBodyTransfer->getTransferData()['name'])) {
            return $eventQueueSendMessageBodyTransfer->getTransferData()['name'];
        }

        if ($eventQueueSendMessageBodyTransfer->getEventName()) {
            $explodedEventName = explode('.', $eventQueueSendMessageBodyTransfer->getEventName());

            return $explodedEventName[1];
        }

        throw new MissingTableNameException('Was not able to resolve table name from message. Neither \'name\' nor \'eventName\' was set');
    }

    /**
     * @param \Generated\Shared\Transfer\QueueReceiveMessageTransfer $queueReceiveMessageTransfer
     *
     * @return \Generated\Shared\Transfer\EventQueueSendMessageBodyTransfer
     */
    public function getEventQueueSendMessageBodyTransfer(QueueReceiveMessageTransfer $queueReceiveMessageTransfer): EventQueueSendMessageBodyTransfer
    {
        return $this->createEventQueueSentMessageBodyTransfer(
            $queueReceiveMessageTransfer->getQueueMessage()->getBody(),
        );
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer $leanPublisherEventCollectionTransfer
     *
     * @return array
     */
    protected function formatFilterCriteria(LeanPublisherEventCollectionTransfer $leanPublisherEventCollectionTransfer): array
    {
        $formattedFilterCriteria = [];
        foreach ($leanPublisherEventCollectionTransfer->getEvents() ?? [] as $event) {
            $formattedFilterCriteria[$event->getEventName()] = $event->getFilterProperties();
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
     * @param \Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer $queueMessageCollectionTransfer
     *
     * @return void
     */
    public function markMessagesAcknowledged(LeanPublisherQueueMessageCollectionTransfer $queueMessageCollectionTransfer): void
    {
        array_map(
            static function ($data) {
                if ($data instanceof QueueReceiveMessageTransfer) {
                    $data->setAcknowledge(true);
                }

                return $data;
            },
            array_merge(
                $queueMessageCollectionTransfer->getValidatedMessages()->getArrayCopy(),
                $queueMessageCollectionTransfer->getInvalidMessages()->getArrayCopy()
            ),
        );
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
        $this->getLogger()->info('[async] ' . $message);
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
