<?php namespace Digbang\Doctrine\Commands\Migrations;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\DBAL\Migrations\Migration;
use Doctrine\DBAL\Migrations\MigrationException;

/**
 * Command for manually adding and deleting migration versions from the version table.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
class VersionCommand extends AbstractMigrationCommand
{
    protected $name = 'doctrine:migrations:version';

    protected $description = 'Manually add and delete migration versions from the version table.';

    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>%command.name%</info> command allows you to manually add and delete migration versions from the version table:

    <info>%command.full_name% YYYYMMDDHHMMSS --add</info>

If you want to delete a version you can use the <comment>--delete</comment> option:

    <info>%command.full_name% YYYYMMDDHHMMSS --delete</info>
EOT
        );
    }

    public function fire()
    {
        $configuration = $this->getMigrationConfiguration();
        $migration = new Migration($configuration);

        if ($this->option('add') === false && $this->option('delete') === false) {
            throw new \InvalidArgumentException('You must specify whether you want to --add or --delete the specified version.');
        }

        $version = $this->argument('version');
        $markMigrated = $this->option('add') ? true : false;

        if ( ! $configuration->hasVersion($version)) {
            throw MigrationException::unknownMigrationVersion($version);
        }

        $version = $configuration->getVersion($version);
        if ($markMigrated && $configuration->hasVersionMigrated($version)) {
            throw new \InvalidArgumentException(sprintf('The version "%s" already exists in the version table.', $version));
        }

        if ( ! $markMigrated && ! $configuration->hasVersionMigrated($version)) {
            throw new \InvalidArgumentException(sprintf('The version "%s" does not exists in the version table.', $version));
        }

        if ($markMigrated) {
            $version->markMigrated();
        } else {
            $version->markNotMigrated();
        }
    }

    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
            ['version', InputArgument::REQUIRED, 'The version to add or delete.', null]
        ]);
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['add', null, InputOption::VALUE_NONE, 'Add the specified version.'],
            ['delete', null, InputOption::VALUE_NONE, 'Delete the specified version.'],
        ]);
    }
}
