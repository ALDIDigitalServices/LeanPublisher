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
use Generated\Shared\Transfer\LeanPublisherQueueMessageCollectionTransfer;
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
     * @param array $filterCriteria
     * @param int $expectedCount
     *
     * @return void
     */
    public function testFilterQueueMessageTransferFiltersQueueMessagesByFilterCriteria(array $filterCriteria, int $expectedCount): void
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

        $leanPublisherQueueMessageCollection = (new LeanPublisherQueueMessageCollectionTransfer())
            ->setValidMessages(new ArrayObject($eventQueueReceiveMessages));

        // act
        $filteredQueueMessageTransfers = $this->tester->getMessageTransferManager()
            ->filterQueueMessageTransfers(
                $leanPublisherQueueMessageCollection,
                $filterCriteria,
            );


        // assert
        $this->assertCount($expectedCount, $filteredQueueMessageTransfers->getValidMessages());
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
        return [
            [
                [
                    ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_CREATE,
                    ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE => [
                        SpyProductOfferTableMap::COL_APPROVAL_STATUS,
                    ],
                ],
                1, // expected count after filtering
            ],
            [
                [
                    ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_CREATE,
                    ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE => [
                        SpyProductOfferTableMap::COL_COMPARISON_PRICE,
                    ],
                ],
                0, // expected count after filtering
            ],
            [
                [
                    ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_CREATE,
                    ProductOfferEvents::ENTITY_SPY_PRODUCT_OFFER_UPDATE => [
                        SpyProductOfferTableMap::COL_APPROVAL_STATUS,
                        SpyProductOfferTableMap::COL_CONCRETE_SKU,
                    ],
                ],
                2, // expected count after filteringF
            ],
        ];
    }
}
