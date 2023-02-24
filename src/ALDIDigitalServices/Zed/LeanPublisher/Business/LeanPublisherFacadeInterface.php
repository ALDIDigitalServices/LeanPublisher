<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business;

use Generated\Shared\Transfer\LeanPublisherResynchronizationRequestTransfer;

interface LeanPublisherFacadeInterface
{
    /**
     * Specification:
     * - processes lean publisher messages from queues
     * - processing contains publishing to database and synchronizing to search/storage
     *
     * @api
     *
     * @param array $queueReceiveMessageTransfers
     *
     * @return array
     */
    public function processLeanPublisherMessages(array $queueReceiveMessageTransfers): array;

    /**
     * Specification:
     * - used to resyncrhonize already published data from database to search/storage via console command
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\LeanPublisherResynchronizationRequestTransfer $leanPublisherResynchronizationRequestTransfer
     *
     * @return void
     */
    public function resynchronizePublishedData(
        LeanPublisherResynchronizationRequestTransfer $leanPublisherResynchronizationRequestTransfer
    ): void;

    /**
     * Specification:
     * - returns resource names of registered aldi publisher event handler plugins
     * - used to show help for console command to resynchronize published entities
     *
     * @api
     *
     * @return array
     */
    public function getAvailableResourceNames(): array;
}
