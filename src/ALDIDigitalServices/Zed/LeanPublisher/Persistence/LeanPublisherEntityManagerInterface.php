<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Persistence;

use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;

interface LeanPublisherEntityManagerInterface
{
    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return void
     */
    public function writePublishData(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): void;

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return void
     */
    public function deletePublishData(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): void;
}
