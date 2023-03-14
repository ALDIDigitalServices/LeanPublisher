<?php

namespace ALDIDigitalServices\Zed\LeanPublisher;

use Spryker\Zed\Kernel\AbstractBundleConfig;

class LeanPublisherConfig extends AbstractBundleConfig
{
    /**
     * @var int
     */
    protected const MESSAGE_PROCESSING_CHUNK_SIZE = 500;

    /**
     * @var int
     */
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
