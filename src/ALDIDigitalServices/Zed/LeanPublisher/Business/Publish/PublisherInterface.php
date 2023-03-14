<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Publish;

use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;

interface PublisherInterface
{
    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    public function publishData(
        LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
    ): LeanPublishAndSynchronizationRequestTransfer;
}
