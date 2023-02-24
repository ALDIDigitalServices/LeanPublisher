<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Synchronization;

use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherSearchPublishPluginInterface;
use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;
use Generated\Shared\Transfer\SearchContextTransfer;
use Generated\Shared\Transfer\SearchDocumentTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Communication\LeanPublisherCommunicationFactory getFactory()
 */
class SearchSynchronizationPlugin extends AbstractPlugin implements LeanPublisherSynchronizationPluginInterface
{
    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandler
     *
     * @return bool
     */
    public function isApplicable(LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandler): bool
    {
        return $leanPublisherEventHandler instanceof LeanPublisherSearchPublishPluginInterface;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return void
     */
    public function synchronize(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): void
    {
        $this->deleteBulk($leanPublishAndSynchronizationRequestTransfer);
        $this->writeBulk($leanPublishAndSynchronizationRequestTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return void
     */
    public function writeBulk(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): void
    {
        $writeDataSets = $this->prepareSearchDocumentTransfers(
            $leanPublishAndSynchronizationRequestTransfer->getSyncDataWrite(),
            $leanPublishAndSynchronizationRequestTransfer->getElasticSearchIndex()
        );

        if ($writeDataSets === []) {
            return;
        }

        $this->getFactory()->getSearchClient()->writeDocuments($writeDataSets);
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return void
     */
    public function deleteBulk(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): void
    {
        $searchDocumentTransfers = $this->prepareSearchDocumentTransfers(
            $leanPublishAndSynchronizationRequestTransfer->getSyncDataDelete(),
            $leanPublishAndSynchronizationRequestTransfer->getElasticSearchIndex()
        );

        if ($searchDocumentTransfers === []) {
            return;
        }

        $this->getFactory()->getSearchClient()->deleteDocuments($searchDocumentTransfers);
    }

    /**
     * @param array $data
     * @param string $elasticSearchIndex
     *
     * @return \Generated\Shared\Transfer\SearchDocumentTransfer[]
     */
    protected function prepareSearchDocumentTransfers(array $data, string $elasticSearchIndex): array
    {
        $searchDocumentTransfers = [];
        foreach ($data as $datum) {
            $key = $datum->getKeySearch();

            $data = $this->getFactory()
                ->getUtilEncodingService()
                ->decodeJson($datum->getDataSearch(), true);

            $searchContext = (new SearchContextTransfer())
                ->setSourceIdentifier($elasticSearchIndex);

            $searchDocumentTransfer = (new SearchDocumentTransfer())
                ->setId($key)
                ->setData($data)
                ->setSearchContext($searchContext);

            $searchDocumentTransfers[] = $searchDocumentTransfer;
        }

        return $searchDocumentTransfers;
    }
}
