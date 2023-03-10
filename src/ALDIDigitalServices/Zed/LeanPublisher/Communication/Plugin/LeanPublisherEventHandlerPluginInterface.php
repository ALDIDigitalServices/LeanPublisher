<?php

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin;

use Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer;

interface LeanPublisherEventHandlerPluginInterface
{
    /**
     * Specification:
     *  - gets subscribed events for this event handler
     *  - events can contain a list of properties which only should lead to processing of the message
     *  - this makes it possible to define if an entity should be published and synchronized or not when certain entity property has changed
     *
     * @link https://confluence.aldi-sued.com/display/ACI/ALDI+Publisher
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\LeanPublisherEventCollectionTransfer
     */
    public function getSubscribedEventCollection(): LeanPublisherEventCollectionTransfer;

    /**
     * Specification:
     * - returns the queue name this plugin is responsible for
     *
     * @api
     *
     * @return string
     */
    public function getQueueName(): string;

    /**
     * Specification:
     * - returns the table query class of the table the search/storage data should be stored to for recovery reasons (re-synchronization)
     *
     * @api
     *
     * @return string
     */
    public function getPublishTableQueryClass(): string;

    /**
     * Specification:
     * - loads entities to be published from database.
     *
     * @api
     *
     * @param array $queueMessages
     *
     * @return array
     */
    public function loadData(array $queueMessages): array;

    /**
     * Specification:
     * - returns resource name for the entity
     *
     * @api
     *
     * @return string
     */
    public function getResourceName(): string;
}
