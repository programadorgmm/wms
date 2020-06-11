<?php

namespace Natue\Bundle\CoreBundle\Robo\Task;

use Robo\Task\ExecTask;

/**
 * Abstraction of ExecTask but it exists in case of an error
 *
 * ``` php
 * <?php
 * $this->taskExecAndExitOnError('compass')->arg()->run();
 *
 * $this->taskExecAndExitOnError('compass watch')->background()->run();
 *
 * if ($this->taskExecAndExitOnError('phpunit .')->run()->wasSuccessful()) {
 *     $this->say('tests passed');
 * }
 * ```
 */
class ExecAndExitOnError extends ExecTask
{
    /**
     * @return \Robo\Result
     * @throws \Exception
     */
    public function run()
    {
        $result = parent::run();

        if ($result->getExitCode() !== 0) {
            throw new \Exception('Exec failed: ' . $this->command);
        }

        return $result;
    }
}
