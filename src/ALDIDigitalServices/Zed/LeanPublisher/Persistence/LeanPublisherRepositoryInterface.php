<?php

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
