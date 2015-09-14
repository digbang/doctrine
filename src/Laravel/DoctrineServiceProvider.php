<?php namespace Digbang\Doctrine\Laravel;

use Digbang\Doctrine\Commands;
use Digbang\Doctrine\EntityManagerFactory;
use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Doctrine\Types;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\NamingStrategy;
use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/config/cache.php', 'doctrine-cache');
		$this->mergeConfigFrom(__DIR__ . '/config/doctrine.php', 'doctrine');
		$this->mergeConfigFrom(__DIR__ . '/config/mappings.php', 'doctrine-mappings');

		$this->registerNamingStrategy();
		$this->registerEntityManager();
        $this->registerTypes();
		$this->registerDecoupledMappingDriver();
	}

	private function registerNamingStrategy()
	{
		$this->app->singleton(NamingStrategy::class, LaravelNamingStrategy::class);
	}

	private function registerEntityManager()
	{
		// bind the EM interface to our only EM as a singleton
		$this->app->singleton(EntityManagerInterface::class, EntityManager::class);

		// bind the EM concrete
		$this->app->singleton(EntityManager::class, function(Container $app) {
			$debugbar = null;
			if (isset($app['debugbar']))
			{
				$debugbar = $app['debugbar'];
			}

            return $app->make(EntityManagerFactory::class)->create($debugbar);
		});
	}

	public function boot()
	{
		$configPath = $this->app->make('path.config');

		$this->publishes([
			dirname(__DIR__) . '/config/cache.php'        => $configPath . '/doctrine-cache.php',
			dirname(__DIR__) . '/config/doctrine.php'     => $configPath . '/doctrine.php',
			dirname(__DIR__) . '/config/mappings.php'     => $configPath . '/doctrine-mappings.php',
			dirname(__DIR__) . '/config/repositories.php' => $configPath . '/doctrine-repositories.php',
		], 'config');

        $this->registerAuthDriver();

		$commands = [
            // doctrine:exec
            Commands\Exec\RunSqlCommand::class,
            Commands\Exec\RunDqlCommand::class,
            Commands\Exec\ImportCommand::class,

            // doctrine:clear-cache
            Commands\ClearCache\MetadataCommand::class,
            Commands\ClearCache\QueryCommand::class,
            Commands\ClearCache\ResultCommand::class,

            // doctrine:schema
            Commands\Schema\CreateCommand::class,
            Commands\Schema\UpdateCommand::class,
            Commands\Schema\DropCommand::class,

            // doctrine:validate
            Commands\Validate\ProductionCommand::class,
            Commands\Validate\SchemaCommand::class,

            // doctrine:generate
            Commands\Generate\RepositoriesCommand::class,
            Commands\Generate\EntitiesCommand::class,
            Commands\Generate\ProxiesCommand::class,

            // doctrine:mappings
            Commands\Mappings\InfoCommand::class,
            Commands\Mappings\DescribeCommand::class,
            Commands\Mappings\ConvertCommand::class,
		];

		if (class_exists('Doctrine\DBAL\Migrations\Configuration\Configuration'))
		{
			$commands = array_merge($commands, [
				// doctrine:migrations
	            Commands\Migrations\DiffCommand::class,
	            Commands\Migrations\ExecuteCommand::class,
	            Commands\Migrations\GenerateCommand::class,
	            Commands\Migrations\MigrateCommand::class,
	            Commands\Migrations\StatusCommand::class,
	            Commands\Migrations\VersionCommand::class
			]);
		}
		$this->commands($commands);
	}

    private function registerAuthDriver()
    {
	    $this->app->extend('auth', function(\Illuminate\Auth\AuthManager $auth) {
		    $auth->extend('doctrine', function(Container $container) {
	            return new DoctrineUserProvider(
	                $container[Hasher::class],
	                $container[EntityManager::class],
	                $container['config']['auth.model']
	            );
	        });

		    return $auth;
	    });
    }

    private function registerTypes()
    {
	    Types\TypeExtender::instance()
		    ->add(Types\CarbonType::DATETIMETZ, 'TIMESTAMP(0) WITH TIME ZONE',    Types\CarbonDateTimeTzType::class)
			->add(Types\CarbonType::DATETIME,   'TIMESTAMP(0) WITHOUT TIME ZONE', Types\CarbonDateTimeType::class)
			->add(Types\CarbonType::DATE,       'DATE',                           Types\CarbonDateType::class)
			->add(Types\CarbonType::TIME,       'TIME(0) WITHOUT TIME ZONE',      Types\CarbonTimeType::class)
			->add(Types\TsvectorType::TSVECTOR, 'TSVECTOR',                       Types\TsvectorType::class);
    }

	private function registerDecoupledMappingDriver()
	{
		$this->app->singleton([MappingDriver::class => DecoupledMappingDriver::class], function(Container $app){
			/** @type DecoupledMappingDriver $driver */
			$driver = new DecoupledMappingDriver($app->make(NamingStrategy::class));

			/** @type Repository $config */
			$config = $app->make(Repository::class);

			foreach ($config->get('doctrine-mappings.mappings', []) as $mappingClass)
			{
				$driver->addMapping($this->app->make($mappingClass));
			}

			return $driver;
		});
	}
}
