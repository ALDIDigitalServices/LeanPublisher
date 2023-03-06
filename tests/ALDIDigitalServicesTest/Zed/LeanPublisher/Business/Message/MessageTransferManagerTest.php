<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServicesTest\Zed\LeanPublisher\Business\Message;

use ALDIDigitalServicesTest\Zed\LeanPublisher\LeanPublisherBusinessTester;
use ArrayObject;
use Codeception\TestCase\Test;
use Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer;
use Generated\Shared\Transfer\LeanPublisherEventTransfer;
use Orm\Zed\Product\Persistence\Map\SpyProductAbstractTableMap;
use Orm\Zed\ProductOffer\Persistence\Map\SpyProductOfferTableMap;
use Pyz\Zed\Product\Dependency\ProductEvents;
use Spryker\Zed\ProductOffer\Dependency\ProductOfferEvents;

/**
 * Auto-generated group annotations
 *
 * @group ALDIDigitalServicesTest
 * @group Zed
 * @group LeanPublisher
 * @group Business
 * @group Message
 * @group MessageTransferManagerTest
 * Add your own group annotations below this line
 */
class MessageTransferManagerTest extends Test
{
    /**
     * @var \ALDIDigitalServicesTest\Zed\LeanPublisher\LeanPublisherBusinessTester
     */
    protected LeanPublisherBusinessTester $tester;

    /**
     * @dataProvider getFilterCriteriaAndExpectedCount
     *
     * @param \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer $leanPublisherEventCollectionTransfer
     * @param int $expectedCount
     *
     * @throws \Exception
     * @return void
     */
    public function testFilterQueueMessageTransferFiltersQueueMessagesByFilterCriteria(LeanPublisherEventCollectionTransfer $leanPublisherEventCollectionTransfer, int $expectedCount): void
    {
        // arrange
        $eventQueueReceiveMessages = [];
        $eventQueueReceiveMessages[] = $this->tester->buildQueueReceiveMessageTransfer(
            ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
            SpyProductOfferTableMap::TABLE_NAME,
            [SpyProductOfferTableMap::COL_APPROVAL_STATUS],
        );

        $eventQueueReceiveMessages[] = $this->tester->buildQueueReceiveMessageTransfer(
            ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
            SpyProductOfferTableMap::TABLE_NAME,
            [SpyProductOfferTableMap::COL_CONCRETE_SKU],
        );


        // act
        $filteredQueueMessageTransfers = $this->tester->getMessageTransferManager()
            ->validateAndFilterQueueMessages(
                $eventQueueReceiveMessages,
                $leanPublisherEventCollectionTransfer,
            );


        // assert
        $this->assertCount($expectedCount, $filteredQueueMessageTransfers->getValidatedMessages());
    }

    /**
     * @return void
     */
    public function testGroupQueueMessagesByQueueNamesGroupsMessagesByQueueName(): void
    {
        // arrange
        $eventQueueReceiveMessages = [];
        $eventQueueReceiveMessages[] = $this->tester->buildQueueReceiveMessageTransfer(
            ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
            SpyProductOfferTableMap::TABLE_NAME,
            [SpyProductOfferTableMap::COL_APPROVAL_STATUS],
            'queueName1'
        );

        $eventQueueReceiveMessages[] = $this->tester->buildQueueReceiveMessageTransfer(
            ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
            SpyProductOfferTableMap::TABLE_NAME,
            [SpyProductOfferTableMap::COL_CONCRETE_SKU],
            'queueName2'
        );

        // act
        $groupedQueueMessageTransfer = $this->tester
            ->getMessageTransferManager()
            ->groupQueueMessageTransfersByQueueName($eventQueueReceiveMessages);

        // assert
        $this->assertCount(2, $groupedQueueMessageTransfer);
        $this->assertArrayHasKey('queueName1', $groupedQueueMessageTransfer);
        $this->assertArrayHasKey('queueName2', $groupedQueueMessageTransfer);
    }

    /**
     * @throws \Exception
     * @return void
     */
    public function testGroupTransfersByTableGroupsTransfersByTable(): void
    {
        // arrange
        $eventQueueReceiveMessages = new ArrayObject();
        $eventQueueReceiveMessages->append(
            $this->tester->buildQueueReceiveMessageTransfer(
                ProductEvents::ENTITY_SPY_PRODUCT_ABSTRACT_UPDATE,
                SpyProductAbstractTableMap::TABLE_NAME,
                [SpyProductAbstractTableMap::COL_COLOR_CODE],
            )
        );

        $eventQueueReceiveMessages->append(
            $this->tester->buildQueueReceiveMessageTransfer(
                ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE,
                SpyProductOfferTableMap::TABLE_NAME,
                [SpyProductOfferTableMap::COL_CONCRETE_SKU],
            )
        );

        // act
        $groupedQueueMessageTransfer = $this->tester
            ->getMessageTransferManager()
            ->groupEventTransfersByTable($eventQueueReceiveMessages);

        // assert
        $this->assertCount(2, $groupedQueueMessageTransfer);
        $this->assertArrayHasKey(SpyProductAbstractTableMap::TABLE_NAME, $groupedQueueMessageTransfer);
        $this->assertArrayHasKey(SpyProductOfferTableMap::TABLE_NAME, $groupedQueueMessageTransfer);
    }

    /**
     * @return array[]
     */
    protected function getFilterCriteriaAndExpectedCount(): array
    {
        $event1 = (new LeanPublisherEventCollectionTransfer())
            ->addEvent(
                (new LeanPublisherEventTransfer())->setEventName(ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_CREATE)
            )
            ->addEvent(
                (new LeanPublisherEventTransfer())->setEventName(ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE)
                    ->addFilterProperty(SpyProductOfferTableMap::COL_APPROVAL_STATUS)
            );

        $event2 = (new LeanPublisherEventCollectionTransfer())
            ->addEvent(
                (new LeanPublisherEventTransfer())->setEventName(ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_CREATE)
            )
            ->addEvent(
                (new LeanPublisherEventTransfer())->setEventName(ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE)
                    ->addFilterProperty(SpyProductOfferTableMap::COL_COMPARISON_PRICE)
            );

        $event3 = (new LeanPublisherEventCollectionTransfer())
            ->addEvent(
                (new LeanPublisherEventTransfer())->setEventName(ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_CREATE)
            )
            ->addEvent(
                (new LeanPublisherEventTransfer())->setEventName(ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE)
                    ->addFilterProperty(SpyProductOfferTableMap::COL_APPROVAL_STATUS)
                    ->addFilterProperty(SpyProductOfferTableMap::COL_CONCRETE_SKU)
            );

        return [
                [$event1, 1,], // expected count after filtering
                [$event2, 0,], // expected count after filtering
                [$event3, 2,], // expected count after filtering
        ];
    }
}
