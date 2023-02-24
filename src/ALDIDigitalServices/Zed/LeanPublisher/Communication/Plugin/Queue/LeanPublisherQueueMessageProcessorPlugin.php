<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Queue;

use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Queue\Dependency\Plugin\QueueMessageProcessorPluginInterface;

/**
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Business\LeanPublisherFacadeInterface getFacade()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherConfig getConfig()
 */
class LeanPublisherQueueMessageProcessorPlugin extends AbstractPlugin implements QueueMessageProcessorPluginInterface
{
    /**
     * @param array $queueMessageTransfers
     *
     * @return array|\Generated\Shared\Transfer\QueueReceiveMessageTransfer[]
     */
    public function processMessages(array $queueMessageTransfers): array
    {
        return $this->getFacade()->processLeanPublisherMessages($queueMessageTransfers);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return int
     */
    public function getChunkSize(): int
    {
        return $this->getConfig()->getLeanPublisherMessageProcessingChunkSize();
    }
}
