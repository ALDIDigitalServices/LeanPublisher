<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Publish;

use ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherEntityManagerInterface;
use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;

class Publisher implements PublisherInterface
{
    /**
     * @var \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherEntityManagerInterface
     */
    protected LeanPublisherEntityManagerInterface $entityManager;

    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherEntityManagerInterface $entityManager
     */
    public function __construct(LeanPublisherEntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    public function publishData(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): LeanPublishAndSynchronizationRequestTransfer
    {
        $this->entityManager->writePublishData($leanPublishAndSynchronizationRequestTransfer);
        $this->entityManager->deletePublishData($leanPublishAndSynchronizationRequestTransfer);

        return $leanPublishAndSynchronizationRequestTransfer;
    }
}
