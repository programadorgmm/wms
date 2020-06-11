<?php

namespace Natue\Bundle\ZedBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use Natue\Bundle\StockBundle\Service\StockItemManager;
use Natue\Bundle\ZedBundle\Service\HttpClient;

class PostCurrentStockCommand extends ContainerAwareCommand
{
    /**
     * Setup command usage
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('natue:zed:post-current-stock');
        $this->setDescription('Post current stock (JSON) back to ZED');
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
        /** @var StockItemManager $stockItemManager */
        $stockItemManager = $this->getContainer()->get('natue.stock.item.manager');

        $stockQuantityJson = $stockItemManager->getCurrentSellableStockData();
        /** @var HttpClient $zedHttpClient */
        $zedHttpClient = $this->getContainer()->get('natue.zed.http_client');

        try {
            $output->writeln($stockQuantityJson);
            $responseMessage = $zedHttpClient->postCurrentStock($stockQuantityJson);
            $output->writeln('Sending current stock back to ZED Successful');
            $output->writeln($responseMessage);
        } catch (\DomainException $domainException) {
            $output->writeln('Fail!');
            $output->writeln($domainException->getMessage());
        }

    }
}
