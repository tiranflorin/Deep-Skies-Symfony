<?php

namespace Dso\PlannerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Dso\PlannerBundle\Services\CreateVisibleObjectsTable;

/**
 * Executes the CreateVisibleObjects command
 *
 * Class CreateVisibleObjectsTableCommand
 *
 * @package Dso\PlannerBundle\Command
 */
class CreateVisibleObjectsTableCommand extends ContainerAwareCommand
{
    /** @var OutputInterface */
    private $output;

    /**
     * Configure the command
     */
    protected function configure()
    {
        $this
            ->setName('dso:planner:createVisibleObjectsTable')
            ->setDescription('Create a new table with alt-azimuthal coordinates of visible deep sky objects
                             for a specific location and time settings. Can be run from a controller or as a cron job.')
            ->addOption('latitude', null, InputOption::VALUE_REQUIRED, 'Latitude of the observer')
            ->addOption('longitude', null, InputOption::VALUE_REQUIRED, 'Longitude of the observer')
            ->addOption('dateTime', null, InputOption::VALUE_REQUIRED, 'Date and time of the observing session')
            ->setHelp('
<info>planner:createVisibleObjectsTable</info> How to run this command:

<info>php app/console dso:planner:createVisibleObjectsTable
--latitude=46.767
--longitude=23.583
--dateTime=2014-02-01*12:48:00
</info>
Notice the "*" separator from the dateTime option.
');

    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $latitude = $input->getOption('latitude');
        $longitude = $input->getOption('longitude');
        $dateTime = $input->getOption('dateTime');
        $dateTime = str_replace('*', ' ', $dateTime);
        if (!$this->validateInput($latitude, $longitude, $dateTime)) {
            return;
        }

        /** @var  CreateVisibleObjectsTable $visibleObjectsService */
        $visibleObjectsService = $this->getContainer()->get('dso_planner.visible_objects');
        $visibleObjectsService->setConfigurationDetails($latitude, $longitude, $dateTime);
        try {
            $result = $visibleObjectsService->executeFlow();
            $this->output->writeln('SUCCESS: ' . json_encode($result));
        } catch (Exception $e) {
            $this->output->writeln('ERROR: ' . json_encode(array('message' => $e->getMessage())));
        }
    }

    /**
     * @param string $latitude
     * @param string $longitude
     * @param string $dateTime
     *
     * @return bool
     */
    protected function validateInput($latitude, $longitude, $dateTime)
    {
        if (!isset($latitude)) {
            $this->output->writeln('ERROR: ' . json_encode(array('message' => 'Please specify latitude parameter')));
            $this->output->writeln($this->getHelp());

            return false;
        }

        if (!isset($longitude)) {
            $this->output->writeln('ERROR: ' . json_encode(array('message' => 'Please specify longitude parameter')));
            $this->output->writeln($this->getHelp());

            return false;
        }

        if (!isset($dateTime)) {
            $this->output->writeln('ERROR: ' . json_encode(array('message' => 'Please specify dateTime parameter')));
            $this->output->writeln($this->getHelp());

            return false;
        }

        return true;
    }
}