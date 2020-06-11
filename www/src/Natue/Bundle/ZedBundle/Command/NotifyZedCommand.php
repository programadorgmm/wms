<?php

namespace Natue\Bundle\ZedBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Zed sync command
 */
class NotifyZedCommand extends ContainerAwareCommand
{
    const ITEMS_BACK_TO_READY_KEY = 'items_back_to_ready';
    const ITEMS_FAILED_KEY = 'items_failed';

    /**
     * Setup command usage
     *
     * @return void
     */
    protected function configure()
    {
        $this->setName('natue:zed:notify');
        $this->setDescription('Notify Zed about items status');

        $this->addOption(
            'limit',
            null,
            InputOption::VALUE_REQUIRED,
            'Set a limit for assignment'
        );
        $this->addOption(
            'action',
            null,
            InputOption::VALUE_REQUIRED,
            'Set an action name {clarify_picking_failed, ready_for_picking}'
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
        $redis = $this->getContainer()->get('snc_redis.default');
        $limit = (int) $input->getOption('limit');
        $actionKey = $input->getOption('action');
        /** @var \Natue\Bundle\ZedBundle\Service\HttpClient $zedHttpClient */
        $zedHttpClient = $this->getContainer()->get('natue.zed.http_client');

        $actionMap = [
            'clarify_picking_failed' => [
                'key' => self::ITEMS_FAILED_KEY,
                'method' => function ($zedOrderItemId) use($zedHttpClient) {
                    $zedHttpClient->clarifyPickingFailedForOrderItemId($zedOrderItemId);
                }
            ],
            'ready_for_picking' => [
                'key' => self::ITEMS_BACK_TO_READY_KEY,
                'method' => function ($zedOrderItemId) use($zedHttpClient) {
                    $zedHttpClient->isPickingForOrderItemId($zedOrderItemId);
                }
            ]
        ];

        $output->writeln(sprintf('Start call action %s with %s items' , $actionKey, $limit));

        while($limit >= 0) {
            $action = $actionMap[$actionKey];
            if ($redis->llen($action['key']) <= 0) {
                $output->writeln(sprintf('There is no items for %s ' , $action['key']));
                break;
            }
            $zedOrderItemId = $redis->lpop($action['key']);
            call_user_func($action['method'], $zedOrderItemId);

            $output->writeln(sprintf('%s notified on ZED ', $zedOrderItemId));

            $limit--;
        }

        $output->writeln('Finish action ' . $actionKey);
    }
}
