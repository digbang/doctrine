<?php namespace Digbang\Doctrine\Commands\Migrations;

use Digbang\Doctrine\Migrations\LaravelConfiguration;
use Symfony\Component\Console\Input\InputOption;

/**
 * Command to view the status of a set of migrations.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
class StatusCommand extends AbstractMigrationCommand
{
    protected $name = 'doctrine:migrations';

    protected $description = 'View the status of a set of migrations.';

    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>%command.name%</info> command outputs the status of a set of migrations:

    <info>%command.full_name%</info>

You can output a list of all available migrations and their status with <comment>--show-versions</comment>:

    <info>%command.full_name% --show-versions</info>
EOT
        );
    }

    public function fire()
    {
        $configuration = $this->getMigrationConfiguration();

        $currentVersion = $configuration->getCurrentVersion();
        if ($currentVersion) {
            $currentVersionFormatted = $configuration->formatVersion($currentVersion) . ' (<comment>'.$currentVersion.'</comment>)';
        } else {
            $currentVersionFormatted = 0;
        }
        $latestVersion = $configuration->getLatestVersion();
        if ($latestVersion) {
            $latestVersionFormatted = $configuration->formatVersion($latestVersion) . ' (<comment>'.$latestVersion.'</comment>)';
        } else {
            $latestVersionFormatted = 0;
        }

        $executedMigrations = $configuration->getMigratedVersions();
        $availableMigrations = $configuration->getAvailableVersions();
        $executedUnavailableMigrations = array_diff($executedMigrations, $availableMigrations);
        $numExecutedUnavailableMigrations = count($executedUnavailableMigrations);
        $newMigrations = count($availableMigrations) - count($executedMigrations);

        $this->line("\n <info>==</info> Configuration\n");

        $info = [
            'Name'                              => $configuration->getName() ? $configuration->getName() : 'Doctrine Database Migrations',
            'Database Driver'                   => $configuration->getConnection()->getDriver()->getName(),
            'Database Name'                     => $configuration->getConnection()->getDatabase(),
            'Configuration Source'              => $this->getConfigurationSource($configuration),
            'Migrations Table Name'             => $configuration->getMigrationsTableName(),
            'Migrations Namespace'              => $configuration->getMigrationsNamespace(),
            'Migrations Directory'              => $configuration->getMigrationsDirectory(),
            'Current Version'                   => $currentVersionFormatted,
            'Latest Version'                    => $latestVersionFormatted,
            'Executed Migrations'               => count($executedMigrations),
            'Executed Unavailable Migrations'   => $numExecutedUnavailableMigrations > 0 ? '<error>'.$numExecutedUnavailableMigrations.'</error>' : 0,
            'Available Migrations'              => count($availableMigrations),
            'New Migrations'                    => $newMigrations > 0 ? '<question>' . $newMigrations . '</question>' : 0
        ];
        foreach ($info as $name => $value) {
            $this->line('    <comment>>></comment> ' . $name . ': ' . str_repeat(' ', 50 - strlen($name)) . $value);
        }

        $showVersions = $this->option('show-versions') ? true : false;
        if ($showVersions === true) {
            if ($migrations = $configuration->getMigrations()) {
                $this->line("\n <info>==</info> Available Migration Versions\n");
                $migratedVersions = $configuration->getMigratedVersions();
                foreach ($migrations as $version) {
                    $isMigrated = in_array($version->getVersion(), $migratedVersions);
                    $status = $isMigrated ? '<info>migrated</info>' : '<error>not migrated</error>';
                    $this->line('    <comment>>></comment> ' . $configuration->formatVersion($version->getVersion()) . ' (<comment>' . $version->getVersion() . '</comment>)' . str_repeat(' ', 30 - strlen($name)) . $status);
                }
            }

            if ($executedUnavailableMigrations) {
                $this->line("\n <info>==</info> Previously Executed Unavailable Migration Versions\n");
                foreach ($executedUnavailableMigrations as $executedUnavailableMigration) {
                    $this->line('    <comment>>></comment> ' . $configuration->formatVersion($executedUnavailableMigration) . ' (<comment>' . $executedUnavailableMigration . '</comment>)');
                }
            }
        }
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['show-versions', null, InputOption::VALUE_NONE, 'This will display a list of all available migrations and their status']
        ]);
    }

    protected function getConfigurationSource($configuration)
    {
        if ($configuration instanceof \Doctrine\DBAL\Migrations\Configuration\AbstractFileConfiguration)
        {
            return $configuration->getFile();
        }

        if ($configuration instanceof LaravelConfiguration)
        {
            return 'Laravel config file';
        }

        return 'manually configured';
    }
}
