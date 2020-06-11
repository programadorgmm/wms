<?php

namespace Natue\Bundle\ZedBundle\Service;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;

use Natue\Bundle\ZedBundle\Entity\ZedSynchronizationLog;

/**
 * Zed sync log service
 */
class DbSynchronizerLog
{

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var EntityRepository
     */
    protected $logRepository;

    /**
     * @param Registry $doctrine
     *
     * @return DbSynchronizerLog
     */
    public function __construct(Registry $doctrine)
    {
        $this->entityManager = $doctrine->getManager();

        $this->logRepository = $doctrine->getRepository('NatueZedBundle:ZedSynchronizationLog');
    }

    /**
     * @throws \Exception
     * @return ZedSynchronizationLog
     */
    public function tryReserve()
    {
        $runningTasks = $this->logRepository->findByStatus(ZedSynchronizationLog::STATUS_RUNNING);
        if (!empty($runningTasks)) {
            throw new \Exception('Unable to obtain lock, script is already running');
        }

        return $this->reserve();
    }

    /**
     * @return ZedSynchronizationLog
     */
    public function forceReserve()
    {
        return $this->reserve();
    }

    /**
     * @return ZedSynchronizationLog
     */
    protected function reserve()
    {
        $log = new ZedSynchronizationLog();
        $log->setStatus(ZedSynchronizationLog::STATUS_RUNNING);
        $log->setStartedAt(new \DateTime("now"));

        $this->entityManager->persist($log);
        $this->entityManager->flush();

        return $log;
    }

    /**
     * @param ZedSynchronizationLog $log
     *
     * @return void
     */
    public function release(ZedSynchronizationLog $log)
    {
        $log->setStatus(ZedSynchronizationLog::STATUS_SUCCESS);
        $log->setFinishedAt(new \DateTime("now"));

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * @param ZedSynchronizationLog $log
     *
     * @return void
     */
    public function fail(ZedSynchronizationLog $log)
    {
        $log->setStatus(ZedSynchronizationLog::STATUS_FAILURE);

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * @param $timeDiff
     *
     * @return \DateTime
     */
    public function getFromDateTime($timeDiff)
    {
        $latestSuccessfulTask = $this->logRepository->findOneBy(
            ["status" => ZedSynchronizationLog::STATUS_SUCCESS],
            ["startedAt" => 'DESC']
        );

        $fromTime = (!empty($latestSuccessfulTask))
            ? $latestSuccessfulTask->getStartedAt()->modify($timeDiff)
            : new \DateTime('2000-01-01'); // Synchronize all data (modified after 2000-01-01)

        return $fromTime;
    }
}
