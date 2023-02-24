<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin;

use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;

interface LeanPublisherSearchPublishPluginInterface
{
    /**
     * Specification:
     * - map the data in a format needed by elastic search
     *
     * @api
     *
     * @param array $loadedData
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    public function mapDataForSearch(
        array $loadedData,
        LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
    ): LeanPublishAndSynchronizationRequestTransfer;

    /**
     * Specification:
     * - gets index for elastic search documents
     *
     * @api
     *
     * @return string
     */
    public function getElasticSearchIndex(): string;
}
