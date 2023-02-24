<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Persistence;

use Generated\Shared\Transfer\LeanPublishAndSynchronizationRequestTransfer;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion;
use Propel\Runtime\ActiveQuery\ModelCriteria;
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
        $publishDataWrite = $leanPublishAndSynchronizationRequestTransfer->getPublishDataWrite();

        if ($publishDataWrite === null || empty($publishDataWrite->getData())) {
            return;
        }

        $writeData = $publishDataWrite->getData()->getArrayCopy();

        $synchronizationEntities = $this->getFactory()->createQueryInstance($leanPublishAndSynchronizationRequestTransfer->getQueryClass())
            ->filterByReference_In(array_keys($writeData))
            ->find();

        $writeEntities = [];
        foreach ($writeData as $reference => $writeItem) {
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
            $entity->fromArray($writeItem->toArray());

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
        $publishDataDelete = $leanPublishAndSynchronizationRequestTransfer->getPublishDataDelete();

        if ($publishDataDelete === null || empty($publishDataDelete->getData())) {
            return;
        }

        $references = [];
        $originIds = [];
        foreach ($publishDataDelete->getData() as $deleteItem) {
            $originIds[] = $deleteItem->getIdOrigin();
            $references[] = $deleteItem->getReference();
        }

        $originIds = array_filter($originIds);
        $references = array_filter($references);

        if (empty($references) && empty($originIds)) {
            return;
        }

        $query = $this->getFactory()->createQueryInstance($leanPublishAndSynchronizationRequestTransfer->getQueryClass());

        if ($originIds) {
            $idOriginCriterion = $this->createCriterion($query, static::COL_ID_ORIGIN, $this->mapToIntItems($originIds));
            $query->addOr($idOriginCriterion);
        }

        if ($references) {
            $referencesCriterion = $this->createCriterion($query, static::COL_REFERENCE, $references);
            $query->addOr($referencesCriterion);
        }

        $result = $query->find();

        if ($result->count()) {
            $entitiesToDelete = $result;
            $entitiesToDelete->delete();
        }

        $leanPublishAndSynchronizationRequestTransfer->setSyncDataDelete($result->getArrayCopy());
    }

    /**
     * @param \Propel\Runtime\ActiveQuery\ModelCriteria $query
     * @param string $column
     * @param mixed $value
     * @param string $comparison
     *
     * @return \Propel\Runtime\ActiveQuery\Criterion\AbstractCriterion
     */
    protected function createCriterion(ModelCriteria $query, string $column, mixed $value, string $comparison = Criteria::IN): AbstractCriterion
    {
        return $query->getNewCriterion(
            $column,
            $value,
            $comparison
        );
    }

    /**
     * @param array $values
     *
     * @return int[]
     */
    protected function mapToIntItems(array $values): array
    {
        return array_map(static fn($value): int => (int)$value, $values);
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
