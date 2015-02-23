<?php namespace Digbang\Doctrine\Commands\Migrations;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command for executing single migrations up or down manually.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
class ExecuteCommand extends AbstractMigrationCommand
{
    protected $name = 'doctrine:migrations:execute';

    protected $description = 'Execute a single migration version up or down manually.';

    protected function configure()
    {
        $this
            ->setHelp(<<<EOT
The <info>%command.name%</info> command executes a single migration version up or down manually:

    <info>%command.full_name% YYYYMMDDHHMMSS</info>

If no <comment>--up</comment> or <comment>--down</comment> option is specified it defaults to up:

    <info>%command.full_name% YYYYMMDDHHMMSS --down</info>

You can also execute the migration as a <comment>--dry-run</comment>:

    <info>%command.full_name% YYYYMMDDHHMMSS --dry-run</info>

You can output the would be executed SQL statements to a file with <comment>--write-sql</comment>:

    <info>%command.full_name% YYYYMMDDHHMMSS --write-sql</info>

Or you can also execute the migration without a warning message wich you need to interact with:

    <info>%command.full_name% --no-interaction</info>
EOT
        );
    }

    public function fire()
    {
        $version = $this->argument('version');
        $direction = $this->option('down') ? 'down' : 'up';

        $configuration = $this->getMigrationConfiguration();
        $version = $configuration->getVersion($version);

        if ($path = $this->option('write-sql')) {
            $path = is_bool($path) ? getcwd() : $path;
            $version->writeSqlFile($path, $direction);
        } else {
            $noInteraction = $this->option('no-interaction') ? true : false;
            if ($noInteraction === true) {
                $version->execute($direction, $this->option('dry-run') ? true : false);
            } else {
                $confirmation = $this->confirm('<question>WARNING! You are about to execute a database migration that could result in schema changes and data lost. Are you sure you wish to continue? (y/n)</question>', false);
                if ($confirmation === true) {
                    $version->execute($direction, $this->option('dry-run') ? true : false);
                } else {
                    $this->line('<error>Migration cancelled!</error>');
                }
            }
        }
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['write-sql', null, InputOption::VALUE_NONE, 'The path to output the migration SQL file instead of executing it.'],
            ['dry-run', null, InputOption::VALUE_NONE, 'Execute the migration as a dry run.'],
            ['up', null, InputOption::VALUE_NONE, 'Execute the migration down.'],
            ['down', null, InputOption::VALUE_NONE, 'Execute the migration down.']
        ]);
    }

    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
            ['version', InputArgument::REQUIRED, 'The version to execute.', null]
        ]);
    }
}
