<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Trait;

use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;
use Generated\Shared\Transfer\LeanPublisherDataCollectionTransfer;
use Generated\Shared\Transfer\LeanPublisherDataTransfer;

trait LeanPublisherDataCollectionTrait
{
    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     * @param \Generated\Shared\Transfer\LeanPublisherDataTransfer $leanPublisherDataTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    public function addToWriteDataCollection(
        LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer,
        LeanPublisherDataTransfer $leanPublisherDataTransfer
    ): \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer {
        $this->requireProperties($leanPublisherDataTransfer);

        $dataCollectionTransfer = $this->getDataCollectionTransfer($leanPublishAndSynchronizationRequestTransfer->getPublishDataWrite());

        if ($dataCollectionTransfer->getData()->offsetExists($leanPublisherDataTransfer->getReference())) {
            $dataCollectionTransfer = $this->extendExistingData($dataCollectionTransfer, $leanPublisherDataTransfer);
            $leanPublishAndSynchronizationRequestTransfer->setPublishDataWrite($dataCollectionTransfer);

            return $leanPublishAndSynchronizationRequestTransfer;
        }

        $dataCollectionTransfer->getData()->offsetSet($leanPublisherDataTransfer->getReference(), $leanPublisherDataTransfer);
        $leanPublishAndSynchronizationRequestTransfer->setPublishDataWrite($dataCollectionTransfer);

        return $leanPublishAndSynchronizationRequestTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     * @param \Generated\Shared\Transfer\LeanPublisherDataTransfer $leanPublisherDataTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer
     */
    public function addToDeleteDataCollection(
        LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer,
        LeanPublisherDataTransfer $leanPublisherDataTransfer
    ): \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer {
        $this->requireProperties($leanPublisherDataTransfer);

        $dataCollectionTransfer = $this->getDataCollectionTransfer($leanPublishAndSynchronizationRequestTransfer->getPublishDataDelete());

        if ($dataCollectionTransfer->getData()->offsetExists($leanPublisherDataTransfer->getReference())) {
            $dataCollectionTransfer = $this->extendExistingData($dataCollectionTransfer, $leanPublisherDataTransfer);
            $leanPublishAndSynchronizationRequestTransfer->setPublishDataDelete($dataCollectionTransfer);

            return $leanPublishAndSynchronizationRequestTransfer;
        }

        $dataCollectionTransfer->getData()->offsetSet($leanPublisherDataTransfer->getReference(), $leanPublisherDataTransfer);
        $leanPublishAndSynchronizationRequestTransfer->setPublishDataDelete($dataCollectionTransfer);

        return $leanPublishAndSynchronizationRequestTransfer;
    }

    /**
     * @param null|\Generated\Shared\Transfer\LeanPublisherDataCollectionTransfer $leanPublisherDataCollectionTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublisherDataCollectionTransfer
     */
    protected function getDataCollectionTransfer(?LeanPublisherDataCollectionTransfer $leanPublisherDataCollectionTransfer): LeanPublisherDataCollectionTransfer
    {
        if ($leanPublisherDataCollectionTransfer === null) {
            $leanPublisherDataCollectionTransfer = new LeanPublisherDataCollectionTransfer();
        }

        return $leanPublisherDataCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherDataCollectionTransfer $dataCollectionTransfer
     * @param \Generated\Shared\Transfer\LeanPublisherDataTransfer $leanPublisherDataTransfer
     *
     * @return \Generated\Shared\Transfer\LeanPublisherDataCollectionTransfer
     */
    protected function extendExistingData(
        LeanPublisherDataCollectionTransfer $dataCollectionTransfer,
        LeanPublisherDataTransfer $leanPublisherDataTransfer
    ): LeanPublisherDataCollectionTransfer {
        $existingData = $dataCollectionTransfer->getData()->offsetGet($leanPublisherDataTransfer->getReference());
        $existingData->fromArray(array_merge($existingData->toArray(), $leanPublisherDataTransfer->modifiedToArray()), true);
        $dataCollectionTransfer->getData()->offsetSet($leanPublisherDataTransfer->getReference(), $existingData);

        return $dataCollectionTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublisherDataTransfer $leanPublisherDataTransfer
     *
     * @return void
     */
    protected function requireProperties(LeanPublisherDataTransfer $leanPublisherDataTransfer): void
    {
        $leanPublisherDataTransfer
            ->requireIdOrigin()
            ->requireReference()
            ->requireStore();
    }
}
