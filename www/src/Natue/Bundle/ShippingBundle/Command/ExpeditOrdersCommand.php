<?php

namespace Natue\Bundle\ShippingBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Natue\Bundle\StockBundle\Service\StockItemManager;
use Natue\Bundle\ZedBundle\Service\HttpClient;

use Doctrine\ORM\EntityManager;

class ExpeditOrdersCommand extends ContainerAwareCommand
{
    /**
     * Setup command usage
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('natue:shipping:expedit-orders');
        $this->setDescription('Expedit Orders Ready For Shipping');
        $this->addOption(
            'limit',
            null,
            InputOption::VALUE_REQUIRED,
            'Set a limit for assingment'
        );
    }

    /**
     * Execute command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            /* @var \Doctrine\ORM\EntityManager $entityManager */
            $entityManager = $this->getContainer()->get('doctrine.orm.entity_manager');

            /** @var HttpClient $zedHttpClient */
            $zedHttpClient = $this->getContainer()->get('natue.zed.http_client');

            /** @var StockItemManager $stockItemManager */
            $stockItemManager = $this->getContainer()->get('natue.stock.item.manager');

            $limit_keys = $input->getOption('limit');

            $redis = $this->getContainer()->get('snc_redis.default');

            $keys = $redis->keys('expeditionOrders:*');

            if (empty($keys)) {
                $output->writeln('There are no orders for expedition');

                return;
            }

            foreach ($keys as $count => $key) {
                $entityManager->getConnection()->beginTransaction();

                if ($count >= $limit_keys) {
                    break;
                }

                $cacheValues = json_decode($redis->get($key));

                $keyName = 'expeditionOrders:'.$cacheValues->logisticsProviderId.':'.$cacheValues->orderId;
                $output->writeln('+++++++++++++++++++++++++++++++++++++++++');
                $output->writeln('Mark Stock Items As Sold...');
                $stockItemManager->markStockItemAsSold($cacheValues->orderId);
                $output->writeln('------------------------------------------');

                $output->writeln('Set Tracking Code For Order Id And');
                $output->writeln('Set Shipped Status For Order Id...');
                if ($zedHttpClient->setTrackingCodeForOrderId($cacheValues->trackingCode, $cacheValues->orderId) and
                    $zedHttpClient->setShippedForOrderId($cacheValues->orderId)) {

                    $output->writeln('------------------------------------------');
                    $output->writeln('Commit Transaction...');
                    $entityManager->getConnection()->commit();

                    $output->writeln('------------------------------------------');
                    $output->writeln('Delete KEY: '. $keyName .' on redis...');
                    $redis->del($keyName);
                    $output->writeln('+++++++++++++++++++++++++++++++++++++++++');
                }
            }

            $output->writeln('The shipping orders was successfully issued');
        } catch (Exception $e) {
            $entityManager->getConnection()->rollback();
            $entityManager->close();
            $output->writeln('ZED POST failure: ' . $e);
        }
    }
}
