<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\Publisher;

interface LeanPublisherTriggerPluginInterface
{
    /**
     * Specification:
     * - Return table name of the entity which should be published.
     * - For example: SpyProductOfferTableMap::TABLE_NAME;
     *
     * @api
     *
     * @return string
     */
    public function getTableName(): string;
}
