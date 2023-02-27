<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class LeanPublisherConfig extends AbstractBundleConfig
{
    protected const MESSAGE_PROCESSING_CHUNK_SIZE = 500;

    protected const RESYNCHRONIZATION_CHUNK_SIZE = 500;

    /**
     * @return int
     */
    public function getLeanPublisherMessageProcessingChunkSize(): int
    {
        return static::MESSAGE_PROCESSING_CHUNK_SIZE;
    }

    /**
     * @return int
     */
    public function getLeanPublisherResynchronizationChunkSize(): int
    {
        return static::RESYNCHRONIZATION_CHUNK_SIZE;
    }
}
