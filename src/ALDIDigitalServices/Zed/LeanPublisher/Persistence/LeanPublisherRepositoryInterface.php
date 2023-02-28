<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Persistence;

use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;

interface LeanPublisherRepositoryInterface
{
    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandler
     * @param array $ids
     * @param int $offset
     * @param int $chunkSize
     *
     * @return array
     */
    public function getResynchronizationData(
        LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandler,
        array $ids,
        int $offset,
        int $chunkSize
    ): array;
}
