<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business;

use Generated\Shared\Transfer\LeanPublisherResynchronizationRequestTransfer;
use Spryker\Zed\Kernel\Business\AbstractFacade;

/**
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Business\LeanPublisherBusinessFactory getFactory()
 */
class LeanPublisherFacade extends AbstractFacade implements LeanPublisherFacadeInterface
{
    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array $queueReceiveMessageTransfers
     *
     * @return array
     */
    public function processLeanPublisherMessages(array $queueReceiveMessageTransfers): array
    {
        return $this->getFactory()->createLeanPublisherEventConsumer()->processLeanPublisherMessages($queueReceiveMessageTransfers);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Generated\Shared\Transfer\LeanPublisherResynchronizationRequestTransfer $leanPublisherResynchronizationRequestTransfer
     *
     * @throws \Exception
     * @return void
     */
    public function resynchronizePublishedData(LeanPublisherResynchronizationRequestTransfer $leanPublisherResynchronizationRequestTransfer): void
    {
        $this->getFactory()
            ->createResynchronization()
            ->resynchronizeData($leanPublisherResynchronizationRequestTransfer);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return array
     */
    public function getAvailableResourceNames(): array
    {
        return $this->getFactory()
            ->createEventHandlerPluginResolver()
            ->getAvailableResourceNames();
    }
}
