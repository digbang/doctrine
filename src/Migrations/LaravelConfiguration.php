<?php namespace Digbang\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Illuminate\Contracts\Config\Repository;

class LaravelConfiguration extends Configuration
{
    public function load(Repository $config)
    {
        $this->setMigrationsDirectory(
            $config->get('doctrine.migrations.directory')
        );
        $this->setMigrationsNamespace(
            $config->get('doctrine.migrations.namespace')
        );

        if ($config->has('doctrine.migrations.table_name'))
        {
            $this->setMigrationsTableName($config->get('doctrine.migrations.table_name'));
        }
    }
}
