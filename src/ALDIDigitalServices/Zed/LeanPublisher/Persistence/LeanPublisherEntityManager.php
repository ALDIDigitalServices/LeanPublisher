<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Persistence;

use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;
use Pyz\Zed\Kernel\Persistence\EntityManager\BatchEntityManagerTrait;
use Spryker\Zed\Kernel\Persistence\AbstractEntityManager;

/**
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherPersistenceFactory getFactory()
 */
class LeanPublisherEntityManager extends AbstractEntityManager implements LeanPublisherEntityManagerInterface
{
    use BatchEntityManagerTrait;

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return void
     */
    public function writePublishData(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): void
    {
        $writeDataCollection = $leanPublishAndSynchronizationRequestTransfer->getPublishDataWrite();

        if (empty($writeDataCollection)) {
            return;
        }

        $synchronizationEntities = $this->getFactory()->createQueryInstance($leanPublishAndSynchronizationRequestTransfer->getQueryClass())
            ->filterByReference_In(array_keys($writeDataCollection))
            ->find();

        $writeEntities = [];
        foreach ($writeDataCollection as $reference => $writeItem) {
            foreach ($synchronizationEntities as $entity) {
                if ($entity->getReference() === $reference) {
                    $entity->fromArray($writeItem);

                    if ($entity->isModified()) {
                        $writeEntities[$reference] = $entity;
                    }

                    continue 2;
                }
            }

            $entity = $this->getFactory()->createEntityFromQueryClass($leanPublishAndSynchronizationRequestTransfer->getQueryClass());
            /* @phpstan-ignore-next-line */
            $entity->fromArray($writeItem);

            $writeEntities[$reference] = $entity;
        }

        $tableMapClass = $this->getTableMapClassName($leanPublishAndSynchronizationRequestTransfer->getQueryClass());

        static::upsertBatch($tableMapClass, $writeEntities);

        $leanPublishAndSynchronizationRequestTransfer->setSyncDataWrite($writeEntities);
    }

    /**
     * @param \Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer
     *
     * @return void
     */
    public function deletePublishData(LeanPublishAndSynchronizationRequestTransfer $leanPublishAndSynchronizationRequestTransfer): void
    {
        $references = array_keys($leanPublishAndSynchronizationRequestTransfer->getPublishDataDelete());

        if (empty($references)) {
            return;
        }
        $result = $this->getFactory()->createQueryInstance($leanPublishAndSynchronizationRequestTransfer->getQueryClass())
            ->filterByReference_In($references)
            ->find();

        if ($result->count()) {
            $entitiesToDelete = $result;
            $entitiesToDelete->delete();
        }

        $leanPublishAndSynchronizationRequestTransfer->setSyncDataDelete($result->getArrayCopy());
    }

    /**
     * @param string $queryClass
     *
     * @return string
     */
    protected function getTableMapClassName(string $queryClass): string
    {
        return $this->getFactory()
            ->createQueryInstance($queryClass)
            ->getTableMap()::class;
    }
}
