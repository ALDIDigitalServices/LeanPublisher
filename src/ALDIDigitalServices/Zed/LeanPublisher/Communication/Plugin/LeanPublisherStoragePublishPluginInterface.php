<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

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
