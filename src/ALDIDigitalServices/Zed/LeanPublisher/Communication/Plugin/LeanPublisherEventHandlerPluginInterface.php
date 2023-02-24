<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Plugin;

interface LeanPublisherEventHandlerPluginInterface
{
    /**
     * Specification:
     *  - gets subscribed events for this event handler
     *
     * @api
     *
     * @return array
     */
    public function getSubscribedEvents(): array;

    /**
     * Specification:
     *  - gets PropertyFilterMapping to filter events to be handled or not
     *  - with this mapping, it is possible to define if an entity should be published and synchronized or not
     *      when certain entity property has changed
     *  - the returned array is expected to be structured into events and properties which need to have been changed
     *  - For example, this mapping would lead to process any messages when <MyEntity> was created
     *    but only when COL_IS_ACTIVE on this entity was updated. Updating any other column would not have an impact.
     *      [
     *          MyEntityEvents::ENTITY_MY_ENTITY_CREATE,
     *          MyEntityEvents::ENTITY_MY_ENTITY_UPDATE => [
     *              MyEntityTableMap::COL_IS_ACTIVE,
     *          ],
     *      ]
     *
     * @link https://confluence.aldi-sued.com/display/ACI/ALDI+Publisher
     *
     * @api
     *
     * @return array
     */
    public function getPropertyFilterMapping(): array;

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
}
