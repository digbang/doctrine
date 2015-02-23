<?php namespace Digbang\Doctrine\Commands\Migrations;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\DBAL\Migrations\Migration;

/**
 * Command for executing a migration to a specified version or the latest available version.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
class MigrateCommand extends AbstractMigrationCommand
{
    protected $name = 'doctrine:migrations:migrate';

    protected $description = 'Execute a migration to a specified version or the latest available version.';

    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>%command.name%</info> command executes a migration to a specified version or the latest available version:

    <info>%command.full_name%</info>

You can optionally manually specify the version you wish to migrate to:

    <info>%command.full_name% YYYYMMDDHHMMSS</info>

You can also execute the migration as a <comment>--dry-run</comment>:

    <info>%command.full_name% YYYYMMDDHHMMSS --dry-run</info>

You can output the would be executed SQL statements to a file with <comment>--write-sql</comment>:

    <info>%command.full_name% YYYYMMDDHHMMSS --write-sql</info>
    
Or you can also execute the migration without a warning message which you need to interact with:
    
    <info>%command.full_name% --no-interaction</info>
    
EOT
        );
    }

    public function fire()
    {
        $version = $this->argument('version');

        $configuration = $this->getMigrationConfiguration();
        $migration = new Migration($configuration);

        $this->outputHeader($configuration);

        $noInteraction = $this->option('no-interaction') ? true : false;

        $executedMigrations = $configuration->getMigratedVersions();
        $availableMigrations = $configuration->getAvailableVersions();
        $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);

        if ($executedUnavailableMigrations) {
            $this->error(sprintf('WARNING! You have %s previously executed migrations in the database that are not registered migrations.', count($executedUnavailableMigrations)));
            foreach ($executedUnavailableMigrations as $executedUnavailableMigration) {
                $this->line('    <comment>>></comment> ' . $configuration->formatVersion($executedUnavailableMigration) . ' (<comment>' . $executedUnavailableMigration . '</comment>)');
            }

            if ($noInteraction === false) {
                $confirmation = $this->confirm('<question>Are you sure you wish to continue? (y/n)</question>', false);
            }
        }

        if ($path = $this->option('write-sql')) {
            $path = is_bool($path) ? getcwd() : $path;
            $migration->writeSqlFile($path, $version);
        } else {
            $dryRun = $this->option('dry-run') ? true : false;
            if ($dryRun === true) {
                $sql = $migration->migrate($version, true);
            } else {
                if ($noInteraction === true) {
                    $sql = $migration->migrate($version, $dryRun);
                } else {
                    $confirmation = $this->confirm('<question>WARNING! You are about to execute a database migration that could result in schema changes and data lost. Are you sure you wish to continue? (y/n)</question>', false);
                    if ($confirmation === true) {
                        $sql = $migration->migrate($version, $dryRun);
                    } else {
                        $this->error('Migration cancelled!');
                    }
                }
            }
            if (!$sql) {
                $this->comment('No migrations to execute.');
            }
        }
    }

    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
            ['version', InputArgument::OPTIONAL, 'The version to migrate to.', null]
        ]);
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['write-sql', null, InputOption::VALUE_NONE, 'The path to output the migration SQL file instead of executing it.'],
            ['dry-run', null, InputOption::VALUE_NONE, 'Execute the migration as a dry run.'],
        ]);
    }
}
