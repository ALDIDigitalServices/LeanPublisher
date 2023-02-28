<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Resynchronization\Iterator;

use Iterator;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherRepositoryInterface;

class ResynchronizationIterator implements Iterator
{
    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface
     */
    protected LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandlerPlugin;

    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherRepositoryInterface
     */
    protected LeanPublisherRepositoryInterface $leanPublisherRepository;

    /**
     * @var array
     */
    protected array $ids = [];

    /**
     * @var int
     */
    protected int $index = 0;

    /**
     * @var array
     */
    protected array $current = [];

    /**
     * @var int
     */
    protected int $chunkSize;

    /**
     * @var int
     */
    protected int $offset = 0;

    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandlerPlugin
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherRepositoryInterface $leanPublisherRepository
     * @param array $ids
     * @param int $chunkSize
     */
    public function __construct(
        LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandlerPlugin,
        LeanPublisherRepositoryInterface $leanPublisherRepository,
        array $ids,
        int $chunkSize
    ) {
        $this->leanPublisherEventHandlerPlugin = $leanPublisherEventHandlerPlugin;
        $this->leanPublisherRepository = $leanPublisherRepository;
        $this->chunkSize = $chunkSize;
        $this->ids = $ids;
    }

    /**
     * @return void
     */
    protected function updateCurrent(): void
    {
        $this->current = $this->leanPublisherRepository->getResynchronizationData($this->leanPublisherEventHandlerPlugin, $this->ids, $this->offset, $this->chunkSize);
    }


    /**
     * @return array
     */
    public function current(): array
    {
        return $this->current;
    }

    /**
     * @return void
     */
    public function next(): void
    {
        $this->offset += $this->chunkSize;
        ++$this->index;
        $this->updateCurrent();
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->index;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current !== [];
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->offset = 0;
        $this->index = 0;
        $this->updateCurrent();
    }
}
