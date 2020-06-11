<?php

namespace Natue\Bundle\StockBundle\Command;

use Doctrine\DBAL\Connection;
use Natue\Bundle\StockBundle\Service\LastInvoiceSynchronizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Last Invoices sync command
 */
class LastInvoiceSynchronizeCommand extends ContainerAwareCommand
{
    /**
     * @var Connection
     */
    protected $wmsConnection;

    /**
     * Setup command usage
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('natue:last-invoices:sync');
        $this->setDescription('Synchronize redis: Last Invoices');
        $this->addOption(
            'force',
            null,
            InputOption::VALUE_NONE,
            'If set, the task will run without checking the lock'
        );
    }

    /**
     * Execute command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        date_default_timezone_set('UTC');

        $output->writeln('Synchronization initialized');

        try {
            /* @var $synchronizerService LastInvoiceSynchronizer */
            $synchronizerService = $this->getContainer()->get('natue.stock.last_invoice.synchronizer');
            $nItems = $synchronizerService->synchronize();

            $output->writeln($nItems.' items sync');
        } catch (\Exception $e) {
            $output->writeln('Synchronization failure.');

            throw $e;
        }

        $output->writeln('Synchronization success.');
    }
}
