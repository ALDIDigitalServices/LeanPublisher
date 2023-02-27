<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

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
        return $this->getFactory()->createLeanPublisherEventConsumer()->processLEanPublisherMessages($queueReceiveMessageTransfers);
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
