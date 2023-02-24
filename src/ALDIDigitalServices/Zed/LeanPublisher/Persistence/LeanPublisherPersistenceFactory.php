<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Persistence;

use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Spryker\Zed\Kernel\Persistence\AbstractPersistenceFactory;

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
