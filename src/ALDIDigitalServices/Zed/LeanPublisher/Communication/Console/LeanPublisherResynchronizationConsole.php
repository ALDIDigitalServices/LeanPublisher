<?php
/**
 * This file is part of the Aldi C&C Project and there for Aldi new Project IP.
 * The license terms agreed between ALDI and Spryker in the Framework Agreement
 * on Software and IT Services under § 10 shall apply.
 */

namespace ALDIDigitalServices\Zed\LeanPublisher\Communication\Console;

use Generated\Shared\Transfer\LeanPublisherResynchronizationRequestTransfer;
use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Business\LeanPublisherFacadeInterface getFacade()
 * @method \ALDIDigitalServices\Zed\LeanPublisher\Communication\LeanPublisherCommunicationFactory getFactory()
 */
class LeanPublisherResynchronizationConsole extends Console
{
    protected const COMMAND_NAME = 'lean-publisher:sync';

    protected const COMMAND_DESCRIPTION = 'Runs synchronization of published entities.';

    protected const RESOURCE = 'resource';

    protected const ENTITY_IDS = 'ids';

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName(static::COMMAND_NAME)
            ->setDescription(static::COMMAND_DESCRIPTION)
            ->addUsage($this->getResourcesUsageText());


        $this->addArgument(
            static::RESOURCE,
            InputArgument::OPTIONAL,
            'Defines which resource(s) should be exported, if there is more than one, use comma to separate them. If not, full export will be executed.'
        );

        $this->addArgument(
            static::ENTITY_IDS,
            InputArgument::OPTIONAL,
            'Defines ids for entities which should be exported, if there is more than one, use comma to separate them. If not, full export will be executed.'
        );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $resources = [];
        $entityIds = [];

        if ($input->getArgument(static::RESOURCE)) {
            $resourceString = $input->getArgument(static::RESOURCE);
            $resources = explode(',', $resourceString); // check if given resource is registered in LeanPublisher
        }

        if ($input->getArgument(static::ENTITY_IDS)) {
            $resourceString = $input->getArgument(static::ENTITY_IDS);
            $entityIds = explode(',', $resourceString);

            $entityIds = array_map(static function ($entityId) {
                return (int)$entityId;
            }, $entityIds);
        }

        if (count($resources) > 1 && !empty($entityIds)) {
            $output->writeln(
                '<error>Resynchronization with specific IDs is possible only for one resource at a time.</error>'
            );

            return Console::CODE_ERROR;
        }

        $leanPublisherResynchronizationRequestTransfer = (new LeanPublisherResynchronizationRequestTransfer())
            ->setResources($resources)
            ->setIds($entityIds);

        $this->getFacade()->resynchronizePublishedData($leanPublisherResynchronizationRequestTransfer);

        return Console::CODE_SUCCESS;
    }

    /**
     * @return string
     */
    protected function getResourcesUsageText(): string
    {
        $availableResourceNames = $this->getFacade()->getAvailableResourceNames();

        return sprintf(
            "[\n\t%s\n]",
            implode(",\n\t", $availableResourceNames)
        );
    }
}
