<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Business\Consumer;

interface LeanPublisherEventConsumerInterface
{
    /**
     * Specification:
     * - groups, filters and processes incoming from messages by instanciating
     *      an instance of \ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin\LeanPublisherEventHandlerPluginInterface
     *      which mentioned in the messages
     * - publishes data to a table defined in a corresponding EventHandlerPlugin
     * - synchronizes data to services like ElasticSearch and Redis
     *
     * @param array $queueReceiveMessageTransfers
     *
     * @return array
     */
    public function processLeanPublisherMessages(array $queueReceiveMessageTransfers): array;
}
