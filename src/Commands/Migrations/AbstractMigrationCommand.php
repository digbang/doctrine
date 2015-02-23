<?php namespace Digbang\Doctrine\Commands\Migrations;

use Digbang\Doctrine\Migrations\LaravelConfiguration;
use Doctrine\DBAL\Migrations\Configuration\AbstractFileConfiguration;
use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Config\Repository;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\DBAL\Migrations\OutputWriter;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\Configuration\YamlConfiguration;
use Doctrine\DBAL\Migrations\Configuration\XmlConfiguration;

/**
 * CLI Command for adding and deleting migration versions from the version table.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
abstract class AbstractMigrationCommand extends Command
{
    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @type EntityManagerInterface
     */
    protected $em;

    /**
     * @type Repository
     */
    private $config;

    public function __construct(EntityManagerInterface $em, Repository $config)
    {
        parent::__construct();

        $this->em = $em;
        $this->config = $config;
    }

    protected function outputHeader(Configuration $configuration)
    {
        $name = $configuration->getName();
        $name = $name ?: 'Doctrine Database Migrations';
        $name = str_repeat(' ', 20) . $name . str_repeat(' ', 20);
        $this->line('<question>' . str_repeat(' ', strlen($name)) . '</question>');
        $this->line('<question>' . $name . '</question>');
        $this->line('<question>' . str_repeat(' ', strlen($name)) . '</question>');
        $this->line('');
    }

    public function setMigrationConfiguration(Configuration $config)
    {
        $this->configuration = $config;
    }

    /**
     * @return Configuration
     */
    protected function getMigrationConfiguration()
    {
        if ( ! $this->configuration)
        {
            $outputWriter = new OutputWriter(function($message){ $this->line($message); });

            switch (true)
            {
                case $this->option('db-configuration'):
                    if (!file_exists($this->option('db-configuration'))) {
                        throw new \InvalidArgumentException("The specified connection file is not a valid file.");
                    }

                    $params = include($this->option('db-configuration'));
                    if (!is_array($params)) {
                        throw new \InvalidArgumentException('The connection file has to return an array with database configuration parameters.');
                    }
                    $conn = \Doctrine\DBAL\DriverManager::getConnection($params);
                    break;
                case file_exists('migrations-db.php'):
                    $params = include("migrations-db.php");
                    if (!is_array($params)) {
                        throw new \InvalidArgumentException('The connection file has to return an array with database configuration parameters.');
                    }
                    $conn = \Doctrine\DBAL\DriverManager::getConnection($params);
                    break;
                case $this->em->getConnection():
                    $conn = $this->em->getConnection();
                    break;
                default:
                    // EntityManager is injected, this shouldn't happen in artisan context...
                    throw new \InvalidArgumentException('You have to specify a --db-configuration file or pass a Database Connection as a dependency to the Migrations.');
            }

            switch (true)
            {
                case $this->option('configuration'):
                    $info = pathinfo($this->option('configuration'));
                    $class = $info['extension'] === 'xml' ? 'Doctrine\DBAL\Migrations\Configuration\XmlConfiguration' : 'Doctrine\DBAL\Migrations\Configuration\YamlConfiguration';
                    /** @type AbstractFileConfiguration $configuration */
                    $configuration = new $class($conn, $outputWriter);
                    $configuration->load($this->option('configuration'));
                    break;
                case ($this->config->has('doctrine::doctrine.migrations.directory') && $this->config->has('doctrine::doctrine.migrations.namespace')):
                    $configuration = new LaravelConfiguration($conn, $outputWriter);
                    $configuration->load($this->config);
                    break;
                case file_exists('migrations.xml'):
                    $configuration = new XmlConfiguration($conn, $outputWriter);
                    $configuration->load('migrations.xml');
                    break;
                case file_exists('migrations.yml'):
                    $configuration = new YamlConfiguration($conn, $outputWriter);
                    $configuration->load('migrations.yml');
                    break;
                default:
                    $configuration = new Configuration($conn, $outputWriter);
            }

            $this->configuration = $configuration;
        }

        return $this->configuration;
    }

    protected function getOptions()
    {
        return [
            ['configuration', null, InputOption::VALUE_OPTIONAL, 'The path to a migrations configuration file.'],
            ['db-configuration', null, InputOption::VALUE_OPTIONAL, 'The path to a database connection configuration file.']
        ];
    }
}
