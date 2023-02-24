<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Synchronization;

use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;

interface LeanPublisherSynchronizationPluginInterface
{
    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return void
     */
    public function synchronize(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): void;

    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandler
     *
     * @return bool
     */
    public function isApplicable(LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandler): bool;
}
