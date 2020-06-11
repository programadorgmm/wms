<?php

namespace Natue\Bundle\CoreBundle\Robo\Traits;

use Natue\Bundle\CoreBundle\Robo\Task\ExecAndExitOnError as TaskExecAndExitOnError;

/**
 * Abstraction of RoboTask Exec but it exists in case of an error
 */
trait ExecAndExitOnError
{
    /**
     * @var array
     */
    private $runningCommands = [];

    /**
     * @param $command
     *
     * @return ExecTask
     */
    protected function taskExecAndExitOnError($command)
    {
        $exec                    = new TaskExecAndExitOnError($command);
        $this->runningCommands[] = $exec;

        return $exec;
    }
}
