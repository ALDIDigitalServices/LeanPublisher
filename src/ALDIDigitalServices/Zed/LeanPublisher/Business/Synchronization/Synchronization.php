<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Synchronization;

use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;

class Synchronization
{
    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Synchronization\LeanPublisherSynchronizationPluginInterface[]
     */
    protected array $synchronizationPlugins;

    /**
     * @param array $synchronizationPlugins
     */
    public function __construct(array $synchronizationPlugins)
    {
        $this->synchronizationPlugins = $synchronizationPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $eventHandlerPlugin
     *
     * @return void
     */
    public function synchronizeData(
        LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer,
        LeanPublisherEventHandlerPluginInterface $eventHandlerPlugin
    ): void {
        foreach ($this->synchronizationPlugins as $synchronizationPlugin) {
            if ($synchronizationPlugin->isApplicable($eventHandlerPlugin)) {
                $synchronizationPlugin->synchronize($leanPublishAndSynchronizationRequestTransfer);
            }
        }
    }
}
