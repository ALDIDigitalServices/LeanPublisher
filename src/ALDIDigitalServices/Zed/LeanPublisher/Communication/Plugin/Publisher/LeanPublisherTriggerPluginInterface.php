<?php

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
