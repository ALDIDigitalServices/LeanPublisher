<?php

namespace ALDIDigitalServicesTest\Zed\LeanPublisher\Business;

use ALDIDigitalServices\Zed\LeanPublisher\Business\Exception\EventHandlerNotFoundException;
use ALDIDigitalServices\Zed\LeanPublisher\Business\Trait\LeanPublisherEventRegistrationTrait;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherDependencyProvider;
use ALDIDigitalServicesTest\Zed\LeanPublisher\LeanPublisherBusinessTester;
use Codeception\TestCase\Test;
use Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer;
use Orm\Zed\ProductOffer\Persistence\Map\SpyProductOfferTableMap;
use Spryker\Zed\ProductOffer\Dependency\ProductOfferEvents;

/**
 * Auto-generated group annotations
 *
 * @group ALDIDigitalServicesTest
 * @group Zed
 * @group LeanPublisher
 * @group Business
 * @group LeanPublisherFacadeTest
 * Add your own group annotations below this line
 */
class LeanPublisherFacadeTest extends Test
{
    use LeanPublisherEventRegistrationTrait;

    /**
     * @var \ALDIDigitalServicesTest\Zed\LeanPublisher\LeanPublisherBusinessTester
     */
    protected LeanPublisherBusinessTester $tester;

    /**
     * @throws \Exception
     * @return void
     */
    public function testLeanPublisherEventConsumerThrowsExceptionWhenEventHandlerNotFound(): void
    {
        // arrange
        $leanPublisherEventHandlerPluginMock = $this->getEventHandlerPluginMock();
        $leanPublisherEventHandlerPluginMock
            ->method('getQueueName')
            ->willReturn('not_existing_queue_name');
        $this->tester->setDependency(LeanPublisherDependencyProvider::PLUGINS_EVENT_HANDLER, [
            $leanPublisherEventHandlerPluginMock->getQueueName() => $leanPublisherEventHandlerPluginMock,
        ]);

        $eventQueueReceiveMessages = [];
        $eventQueueReceiveMessages[] = $this->tester->buildQueueReceiveMessageTransfer(
            ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
            SpyProductOfferTableMap::TABLE_NAME,
            [SpyProductOfferTableMap::COL_APPROVAL_STATUS],
        );

        // expect exception
        $this->expectException(EventHandlerNotFoundException::class);
        $this->expectExceptionMessage(
            sprintf(
                'EventHandlerPlugin for queue name \'%s\' not found',
                LeanPublisherBusinessTester::DEFAULT_QUEUE_NAME
            )
        );

        // act
        $this->tester->getFacade()->processLeanPublisherMessages($eventQueueReceiveMessages);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function testLeanPublisherEventConsumerThrowsNoExceptionWhenEventHandlerFound(): void
    {
        // arrange
        $leanPublisherEventHandlerPluginMock = $this->getEventHandlerPluginMock();
        $leanPublisherEventHandlerPluginMock
            ->method('getQueueName')
            ->willReturn(LeanPublisherBusinessTester::DEFAULT_QUEUE_NAME);
        $this->tester->setDependency(LeanPublisherDependencyProvider::PLUGINS_EVENT_HANDLER, [
            $leanPublisherEventHandlerPluginMock->getQueueName() => $leanPublisherEventHandlerPluginMock,
        ]);

        $eventQueueReceiveMessages = [];
        $eventQueueReceiveMessages[] = $this->tester->buildQueueReceiveMessageTransfer(
            ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
            SpyProductOfferTableMap::TABLE_NAME,
            [SpyProductOfferTableMap::COL_APPROVAL_STATUS],
        );

        // act
        $this->tester->getFacade()->processLeanPublisherMessages($eventQueueReceiveMessages);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function testLeanPublisherEventConsumerMarksMessageHasErrorWhenBodyIsInvalid(): void
    {
        // arrange
        $leanPublisherEventHandlerPluginMock = $this->getEventHandlerPluginMock();
        $leanPublisherEventHandlerPluginMock
            ->method('getQueueName')
            ->willReturn(LeanPublisherBusinessTester::DEFAULT_QUEUE_NAME);

        $leanPublisherEventHandlerPluginMock
            ->method('loadData')
            ->willReturn([]);

        $this->registerForEvent(ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE, [SpyProductOfferTableMap::COL_CONCRETE_SKU]);
        $subscribedEventCollection = $this->getEventCollection();

        $leanPublisherEventHandlerPluginMock
            ->method('getSubscribedEventCollection')
            ->willReturn($subscribedEventCollection);

        $this->tester->setDependency(LeanPublisherDependencyProvider::PLUGINS_EVENT_HANDLER, [
            $leanPublisherEventHandlerPluginMock->getQueueName() => $leanPublisherEventHandlerPluginMock,
        ]);

        $eventQueueReceiveMessages = [];
        $eventQueueReceiveMessages[] = $this->tester->buildQueueReceiveMessageTransfer(
            ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
            SpyProductOfferTableMap::TABLE_NAME,
            [SpyProductOfferTableMap::COL_APPROVAL_STATUS],
            LeanPublisherBusinessTester::DEFAULT_QUEUE_NAME,
            'not_existing_listener_class'
        );

        // act
        $processedMessages = $this->tester->getFacade()->processLeanPublisherMessages($eventQueueReceiveMessages);

        // assert
        $this->assertTrue($processedMessages[0]->getHasError());
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function testLeanPublisherEventConsumerMarksMessagesAcknowledgedWhenNoDataWasFound(): void
    {
        // arrange
        $leanPublisherEventHandlerPluginMock = $this->getEventHandlerPluginMock();
        $leanPublisherEventHandlerPluginMock
            ->method('getQueueName')
            ->willReturn(LeanPublisherBusinessTester::DEFAULT_QUEUE_NAME);


        $this->registerForEvent(ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE, [SpyProductOfferTableMap::COL_APPROVAL_STATUS]);
        $subscribedEventCollection = $this->getEventCollection();

        $leanPublisherEventHandlerPluginMock
            ->method('getSubscribedEventCollection')
            ->willReturn($subscribedEventCollection);

        $this->tester->setDependency(LeanPublisherDependencyProvider::PLUGINS_EVENT_HANDLER, [
            $leanPublisherEventHandlerPluginMock->getQueueName() => $leanPublisherEventHandlerPluginMock,
        ]);

        $eventQueueReceiveMessages = [];
        $eventQueueReceiveMessages[] = $this->tester->buildQueueReceiveMessageTransfer(
            ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
            SpyProductOfferTableMap::TABLE_NAME,
            [SpyProductOfferTableMap::COL_APPROVAL_STATUS],
        );

        // act
        $processedMessages = $this->tester->getFacade()->processLeanPublisherMessages($eventQueueReceiveMessages);


        // assert
        /** @var \Generated\Shared\Transfer\QueueReceiveMessageTransfer $processedMessage */
        foreach ($processedMessages as $processedMessage) {
            $this->assertTrue($processedMessage->getAcknowledge());
        }
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function testMessageIsNotHandledWhenFilterMappingPreventsIt(): void
    {
        // arrange
        $leanPublisherEventHandlerPluginMock = $this->getEventHandlerPluginMock();
        $leanPublisherEventHandlerPluginMock
            ->method('getQueueName')
            ->willReturn(LeanPublisherBusinessTester::DEFAULT_QUEUE_NAME);

        $this->registerForEvent(ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE, [SpyProductOfferTableMap::COL_CONCRETE_SKU]);
        $subscribedEventCollection = $this->getEventCollection();

        $leanPublisherEventHandlerPluginMock
            ->method('getSubscribedEventCollection')
            ->willReturn($subscribedEventCollection);

        // expect load data is never called because message was filtered out
        $leanPublisherEventHandlerPluginMock
            ->expects($this->never())
            ->method('loadData');

        $this->tester->setDependency(LeanPublisherDependencyProvider::PLUGINS_EVENT_HANDLER, [
            $leanPublisherEventHandlerPluginMock->getQueueName() => $leanPublisherEventHandlerPluginMock,
        ]);

        $eventQueueReceiveMessages = [];
        $eventQueueReceiveMessages[] = $this->tester->buildQueueReceiveMessageTransfer(
            ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
            SpyProductOfferTableMap::TABLE_NAME,
            [SpyProductOfferTableMap::COL_APPROVAL_STATUS],
        );

        // act
        $processedMessages = $this->tester->getFacade()->processLeanPublisherMessages($eventQueueReceiveMessages);


        // assert
        /** @var \Generated\Shared\Transfer\QueueReceiveMessageTransfer $processedMessage */
        foreach ($processedMessages as $processedMessage) {
            $this->assertTrue($processedMessage->getAcknowledge());
        }
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function testMessagesAreProcessedIfNoFilterMappingIsGiven(): void
    {
        // arrange
        $leanPublisherEventHandlerPluginMock = $this->getEventHandlerPluginMock();
        $leanPublisherEventHandlerPluginMock
            ->method('getQueueName')
            ->willReturn(LeanPublisherBusinessTester::DEFAULT_QUEUE_NAME);


        $leanPublisherEventHandlerPluginMock
            ->method('getSubscribedEventCollection')
            ->willReturn(new LeanPublisherEventCollectionTransfer()); // no filter mapping given

        // expect load data is called because no message was filtered out
        $leanPublisherEventHandlerPluginMock
            ->expects($this->once())
            ->method('loadData')
            ->willReturn([]);

        $this->tester->setDependency(LeanPublisherDependencyProvider::PLUGINS_EVENT_HANDLER, [
            $leanPublisherEventHandlerPluginMock->getQueueName() => $leanPublisherEventHandlerPluginMock,
        ]);

        $eventQueueReceiveMessages = [];
        $eventQueueReceiveMessages[] = $this->tester->buildQueueReceiveMessageTransfer(
            ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
            SpyProductOfferTableMap::TABLE_NAME,
            [SpyProductOfferTableMap::COL_APPROVAL_STATUS],
        );

        // act
        $processedMessages = $this->tester->getFacade()->processLeanPublisherMessages($eventQueueReceiveMessages);

        // assert
        /** @var \Generated\Shared\Transfer\QueueReceiveMessageTransfer $processedMessage */
        foreach ($processedMessages as $processedMessage) {
            $this->assertTrue($processedMessage->getAcknowledge());
        }
    }

    /**
     * @return \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getEventHandlerPluginMock(): LeanPublisherEventHandlerPluginInterface|\PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(LeanPublisherEventHandlerPluginInterface::class);
    }
}
