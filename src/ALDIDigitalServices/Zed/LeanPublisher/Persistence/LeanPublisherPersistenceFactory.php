<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Persistence;

use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;

/**
 * @method \ALDIDigitalServices\Zed\LeanPublisher\LeanPublisherConfig getConfig()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherRepositoryInterface getRepository()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Persistence\LeanPublisherEntityManagerInterface getEntityManager()
 */
class LeanPublisherPersistenceFactory extends AbstractPersistenceFactory
{
    /**
     * @param string $queryClass
     *
     * @return \Propel\Runtime\ActiveQuery\ModelCriteria
     */
    public function createQueryInstance(string $queryClass): ModelCriteria
    {
        return new $queryClass();
    }

    /**
     * @param string $queryClass
     *
     * @return \Propel\Runtime\ActiveRecord\ActiveRecordInterface
     */
    public function createEntityFromQueryClass(string $queryClass): ActiveRecordInterface
    {
        $queryInstance = $this->createQueryInstance($queryClass);
        $entityClass = $queryInstance->getTableMap()->getClassName();

        return new $entityClass();
    }
}
