<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Synchronization;

use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherStoragePublishPluginInterface;
use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Communication\LeanPublisherCommunicationFactory getFactory()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherConfig getConfig()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Business\LeanPublisherFacadeInterface getFacade()
 */
class StorageSynchronizationPlugin extends AbstractPlugin implements LeanPublisherSynchronizationPluginInterface
{
    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandler
     *
     * @return bool
     */
    public function isApplicable(LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandler): bool
    {
        return $leanPublisherEventHandler instanceof LeanPublisherStoragePublishPluginInterface;
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return void
     */
    public function synchronize(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): void
    {
        $this->writeStorage($leanPublishAndSynchronizationRequestTransfer->getSyncDataWrite());
        $this->deleteStorage($leanPublishAndSynchronizationRequestTransfer->getSyncDataDelete());
    }

    /**
     * @param array $dataToWrite
     *
     * @return void
     */
    protected function writeStorage(array $dataToWrite): void
    {
        $storageWriteMessages = [];
        foreach ($dataToWrite as $message) {
            $key = $message->getKeyStorage();
            $value = $message->getDataStorage();

            $storageWriteMessages[$key] = $value;
        }

        if ($storageWriteMessages === []) {
            return;
        }

        $this->getFactory()
            ->getStorageClient()
            ->setMulti($storageWriteMessages);
    }

    /**
     * @param array $dataToDelete
     *
     * @return void
     */
    public function deleteStorage(array $dataToDelete): void
    {
        $keysToDelete = [];

        foreach ($dataToDelete as $message) {
            $keysToDelete[] = $message->getKeyStorage();
        }

        if ($keysToDelete === []) {
            return;
        }

        $this->getFactory()
            ->getStorageClient()
            ->deleteMulti($keysToDelete);
    }
}
