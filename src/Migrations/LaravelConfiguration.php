<?php namespace Digbang\Doctrine\Migrations;

use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Illuminate\Config\Repository;

class LaravelConfiguration extends Configuration
{
    public function load(Repository $config)
    {
        $this->setMigrationsDirectory(
            $config->get('doctrine::doctrine.migrations.directory')
        );
        $this->setMigrationsNamespace(
            $config->get('doctrine::doctrine.migrations.namespace')
        );

        if ($config->has('doctrine::doctrine.migrations.table_name'))
        {
            $this->setMigrationsTableName($config->get('doctrine::doctrine.migrations.table_name'));
        }
    }
}
