<?php

namespace ALDIDigitalServicesTest\Zed\LeanPublisher;

use ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManager;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManagerInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Event\Listeners\LeanPublisherEventListener;
use Codeception\Actor;
use Generated\Shared\DataBuilder\QueueReceiveMessageBuilder;
use Generated\Shared\Transfer\EventEntityTransfer;
use Generated\Shared\Transfer\EventQueueSendMessageBodyTransfer;
use Generated\Shared\Transfer\QueueReceiveMessageTransfer;
use Generated\Shared\Transfer\QueueSendMessageTransfer;

/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Business\LeanPublisherFacadeInterface getFacade()
 *
 * @SuppressWarnings(PHPMD)
 */
class LeanPublisherBusinessTester extends Actor
{
    use _generated\LeanPublisherBusinessTesterActions;

    /**
     * @var string
     */
    public const DEFAULT_QUEUE_NAME = 'queue_name';

    /**
     * @param string $event
     * @param string $name
     * @param array $modifiedColumns
     * @param string $queueName
     * @param string $listenerClassName
     *
     * @return \Generated\Shared\Transfer\QueueReceiveMessageTransfer
     */
    public function buildQueueReceiveMessageTransfer(
        string $event,
        string $name,
        array $modifiedColumns,
        string $queueName = self::DEFAULT_QUEUE_NAME,
        string $listenerClassName = LeanPublisherEventListener::class
    ): QueueReceiveMessageTransfer {
        $builder = new QueueReceiveMessageBuilder();

        $eventEntityTransfer = (new EventEntityTransfer())
            ->setId(random_int(0, 9999))
            ->setEvent($event)
            ->setName($name)
            ->setModifiedColumns($modifiedColumns);

        $eventQueueSendMessageBodyTransfer = (new EventQueueSendMessageBodyTransfer())
            ->setListenerClassName($listenerClassName)
            ->setTransferClassName(EventEntityTransfer::class)
            ->setEventName($event)
            ->setTransferData($eventEntityTransfer->toArray());

        $queueMessageTransfer = (new QueueSendMessageTransfer())
            ->setBody($this->getLocator()->utilEncoding()->service()
                ->encodeJson(
                    $eventQueueSendMessageBodyTransfer->toArray()
                ));

        return $builder->build()->setQueueMessage($queueMessageTransfer)->setQueueName($queueName);
    }

    /**
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Business\Message\MessageTransferManagerInterface
     */
    public function getMessageTransferManager(): MessageTransferManagerInterface
    {
        return new MessageTransferManager(
            $this->getLocator()->utilEncoding()->service()
        );
    }
}
