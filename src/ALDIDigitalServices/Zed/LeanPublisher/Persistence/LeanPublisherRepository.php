<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Persistence;

use ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface;
use Spryker\Zed\Kernel\Persistence\AbstractRepository;

/**
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherPersistenceFactory getFactory()
 */
class LeanPublisherRepository extends AbstractRepository implements LeanPublisherRepositoryInterface
{
    /**
     * @param \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandler
     * @param int[] $ids
     * @param int $offset
     * @param int $chunkSize
     *
     * @return array
     */
    public function getResynchronizationData(
        LeanPublisherEventHandlerPluginInterface $leanPublisherEventHandler,
        array $ids,
        int $offset,
        int $chunkSize
    ): array {
        $query = $this->getFactory()->createQueryInstance($leanPublisherEventHandler->getPublishTableQueryClass());

        if (!empty($ids)) {
            $query->filterByPrimaryKeys($ids);
        }

        $query
            ->setOffset($offset)
            ->setLimit($chunkSize);

        return $query->find()->getData();
    }
}
