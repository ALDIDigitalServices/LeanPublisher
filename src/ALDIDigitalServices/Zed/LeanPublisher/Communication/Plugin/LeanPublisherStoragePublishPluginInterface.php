<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin;

use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;

interface LeanPublisherStoragePublishPluginInterface
{
    /**
     * Specification:
     * - map the data in a format needed by storage
     *
     * @api
     *
     * @param array $loadedData
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    public function mapDataForStorage(array $loadedData, LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): LeanPublishAndSynchronizationRequestTransfer;
}
