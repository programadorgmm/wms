<?php

use Robo\Tasks;

use Natue\Bundle\CoreBundle\Robo\Traits\ExecAndExitOnError;

/**
 * Robofile
 */
class Robofile extends Tasks
{
    use ExecAndExitOnError;

    /**
     * @var string
     */
    protected $env = 'dev';

    /**
     * Full build
     *
     * @return void
     */
    public function build()
    {
        $this->runAllTasks();
    }

    /**
     * Full prod build
     *
     * @return void
     */
    public function buildProd()
    {
        $this->env = 'prod';

        $this->runAllTasks();
    }

    /**
     * @return void
     */
    private function runAllTasks()
    {
        $this->lintPhp();
        $this->lintTwig();
        $this->csPhp();
        $this->assets();
        $this->migrations();
        $this->cacheClear();
        $this->cacheWarmup();
        $this->doctrineClearMetadata();
        $this->testPhpunit();
    }

    /**
     * Assets
     *
     * @return void
     */
    public function assets()
    {
        $this->taskExecAndExitOnError('app/console')
            ->args(['assets:install', '--symlink', 'web'])
            ->run();

        /**
         * Fix for a Symfony bug
         *
         * The server variables overwrite the config variables
         * and NODE_PATH would be "/usr/local/lib/node" but
         * we want to use the local "node_modules"
         */
        $this->taskExecAndExitOnError('unset NODE_PATH && app/console')
            ->args(['assetic:dump', '--env', $this->env])
            ->run();
    }

    /**
     * Migrations
     *
     * @return void
     */
    public function migrations()
    {
        $this->taskExecAndExitOnError('app/console')
            ->args(['doctrine:migrations:migrate', '--no-interaction', '--env', $this->env])
            ->run();
    }

    /**
     * Clear doctrine's entities cache
     *
     * @return void
     */
    public function doctrineClearMetadata()
    {
        $this->taskExecAndExitOnError('app/console')
            ->args(['doctrine:cache:clear-metadata', '--env', $this->env])
            ->run();
    }

    /**
     * Clear cache
     *
     * @return void
     */
    public function cacheClear()
    {
        $this->taskExecAndExitOnError('app/console')
            ->args(['cache:clear', '--env', $this->env])
            ->run();
    }

    /**
     * Warm-up cache
     *
     * @return void
     */
    public function cacheWarmup()
    {
        $this->taskExecAndExitOnError('app/console')
            ->args(['cache:warmup', '--env', $this->env])
            ->run();
    }

    /**
     * Reset database for test environment
     *
     * @return void
     */
    public function testResetDatabase()
    {
        $this->env = 'test';

        $this->taskExecAndExitOnError('app/console')
            ->args(['doctrine:schema:drop --force', '--env', $this->env])
            ->run();

        $this->taskExecAndExitOnError('app/console')
            ->args(['doctrine:schema:create', '--env', $this->env])
            ->run();

        $this->taskExecAndExitOnError('app/console')
            ->args(['doctrine:schema:update --force', '--env', $this->env])
            ->run();
    }

    /**
     * Load fixtures for test environment
     *
     * @return void
     */
    public function testFixturesLoad()
    {
        $this->env = 'test';

        $this->taskExecAndExitOnError('app/console')
            ->args(['doctrine:fixtures:load --no-interaction', '--env', $this->env])
            ->run();
    }

    /**
     * Execute phpunit with config
     *
     * @param null $args
     */
    public function testPhpunit($args = null)
    {
        $this->testResetDatabase();
        $this->testFixturesLoad();

        $this->taskExecAndExitOnError('bin/phpunit')
            ->args(['-c', 'app/phpunit.xml'])
            ->args($args)
            ->run();

        $this->say('Coverage at app/build/coverage/index.html');
    }

    /**
     * Execute phpunit with config on CircleCi
     *
     * @param null $args
     */
    public function testPhpunitCircleCI($args = null)
    {
        $this->env = 'circleci';

        $this->taskExecAndExitOnError('bin/phpunit')
            ->args(['-c', 'app/phpunit_circleci.xml'])
            ->args($args)
            ->run();

        $this->say('Coverage at app/build/coverage/index.html');
    }

    /**
     * Execute PHP Lint
     *
     * @return void
     */
    public function lintPhp()
    {
        $this->taskExecAndExitOnError('find src -name "*.php" -exec php -l {} \;')
            ->run();
    }

    /**
     * Execute TWIG Lint
     *
     * @return void
     */
    public function lintTwig()
    {
        $this->taskExecAndExitOnError('app/console')
            ->args(['twig:lint', '--env', $this->env, 'src'])
            ->run();
    }

    /**
     * Execute PHPCS
     *
     * @return void
     */
    public function csPhp()
    {
        $this->taskExecAndExitOnError('bin/phpcs')
            ->args(['--extensions=php', '--standard=PSR2', 'src'])
            ->run();
    }
}
