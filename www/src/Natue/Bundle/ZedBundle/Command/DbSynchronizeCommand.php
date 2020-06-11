<?php

namespace Natue\Bundle\ZedBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\DBAL\Connection;

use Natue\Bundle\ZedBundle\Service\DbSynchronizer;
use Natue\Bundle\ZedBundle\Service\DbSynchronizerLog;

/**
 * Zed sync command
 */
class DbSynchronizeCommand extends ContainerAwareCommand
{
    /**
     * Tables/Views to synchronize between ZED and WMS.
     *
     * @var array
     */
    protected static $tables = [
        'supplier',
        'product',
        'product_barcode',
        'order_item_status',
        'order',
        'order_item',
        'order_item_status_history',
        'supplier_sku',
        'supplier_shipping_unit_sku',
        'supplier_shipping_unit_barcode',
        'supplier_barcode',
    ];

    /**
     * Select all rows from last successful
     * synchronization minus TIME_DIFF
     * in a format accepted by strtotime()
     *
     * @var string
     */
    const TIME_DIFF = "-2 hours";

    /**
     * @var Connection
     */
    protected $wmsConnection;

    /**
     * @var DbSynchronizerLog
     */
    protected $logService;

    /**
     * Setup command usage
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('natue:zed:db-sync');
        $this->setDescription('Synchronize database: ZED views into WMS tables');
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
        $this->logService = $this->getContainer()->get('natue.zed.synchronizer_log');

        $lock = ($input->getOption('force'))
            ? $this->logService->forceReserve()
            : $this->logService->tryReserve();

        $this->wmsConnection = $this->getContainer()->get('doctrine.dbal.default_connection');
        $this->wmsConnection->beginTransaction();

        try {
            /* @var $synchronizerService DbSynchronizer */
            $synchronizerService = $this->getContainer()->get('natue.zed.synchronizer');
            $synchronizerService->synchronize(
                self::$tables,
                $this->logService->getFromDateTime(self::TIME_DIFF)
            );

            $this->wmsConnection->commit();
        } catch (\Exception $e) {
            $this->wmsConnection->rollback();
            $this->wmsConnection->close();

            $this->logService->fail($lock);
            $output->writeln('Synchronization failure.');
            throw $e;
        }

        $this->logService->release($lock);
        $output->writeln('Synchronization success.');
    }
}
