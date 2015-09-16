<?php namespace Digbang\Doctrine\Laravel;

use Digbang\Doctrine\Bridges\CacheBridge;
use Digbang\Doctrine\Bridges\EventManagerBridge;
use Digbang\Doctrine\Commands;
use Digbang\Doctrine\EntityManagerFactory;
use Digbang\Doctrine\Events\EntityManagerCreating;
use Digbang\Doctrine\Listeners\DBVersionPersister;
use Digbang\Doctrine\Listeners\DebugLogging;
use Digbang\Doctrine\Listeners\SoftDeletableListener;
use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Doctrine\Types;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\DBAL\Events as DBALEvents;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
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
		$this->mergeConfigFrom(dirname(dirname(__DIR__)) . '/config/cache.php', 'doctrine-cache');
		$this->mergeConfigFrom(dirname(dirname(__DIR__)) . '/config/doctrine.php', 'doctrine');
		$this->mergeConfigFrom(dirname(dirname(__DIR__)) . '/config/mappings.php', 'doctrine-mappings');

		$this->registerDoctrineImplementations();
		$this->registerEntityManager();
        $this->registerTypes();
		$this->registerDecoupledMappingDriver();
	}

	/**
	 * Boot the service provider.
	 *
	 * @param EventManager  $eventManager
	 * @param CacheProvider $cache
	 * @param Repository    $config
	 */
	public function boot(EventManager $eventManager, CacheProvider $cache, Repository $config)
	{
		$this->publishConfiguration();
		$this->addEventListeners($eventManager, $cache, $config);
		$this->extendAuthDriver();
		$this->addConsoleCommands();
	}

	/**
	 * Register Doctrine interfaces in Laravel's Container
	 */
	private function registerDoctrineImplementations()
	{
		$this->app->singleton(NamingStrategy::class, LaravelNamingStrategy::class);
		$this->app->singleton(EventManager::class,   EventManagerBridge::class);
		$this->app->singleton(CacheProvider::class,  CacheBridge::class);
	}

	/**
	 * Register the EM Factory as the EM resolver.
	 */
	private function registerEntityManager()
	{
		$this->app->singleton([EntityManagerInterface::class => EntityManager::class], function(Container $app) {
            return $app->make(EntityManagerFactory::class)->create();
		});
	}

	/**
	 * Register custom types in the TypeExtender
	 */
	private function registerTypes()
	{
		Types\TypeExtender::instance()
			->add(Types\CarbonType::DATETIMETZ, 'TIMESTAMP(0) WITH TIME ZONE',    Types\CarbonDateTimeTzType::class)
			->add(Types\CarbonType::DATETIME,   'TIMESTAMP(0) WITHOUT TIME ZONE', Types\CarbonDateTimeType::class)
			->add(Types\CarbonType::DATE,       'DATE',                           Types\CarbonDateType::class)
			->add(Types\CarbonType::TIME,       'TIME(0) WITHOUT TIME ZONE',      Types\CarbonTimeType::class)
			->add(Types\TsvectorType::TSVECTOR, 'TSVECTOR',                       Types\TsvectorType::class);
	}

	/**
	 * Register the mapping driver
	 */
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

	/**
	 * Publish configurations so users can import them to their config dir
	 */
	private function publishConfiguration()
	{
		$configPath = $this->app->make('path.config');

		$this->publishes([
			dirname(dirname(__DIR__)) . '/config/cache.php'        => $configPath . '/doctrine-cache.php',
			dirname(dirname(__DIR__)) . '/config/doctrine.php'     => $configPath . '/doctrine.php',
			dirname(dirname(__DIR__)) . '/config/mappings.php'     => $configPath . '/doctrine-mappings.php',
		], 'config');
	}

	/**
	 * Extend the AuthManager with a Doctrine driver.
	 */
    private function extendAuthDriver()
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

	/**
	 * Add doctrine commands to artisan
	 */
	private function addConsoleCommands()
	{
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

	/**
	 * Add base doctrine events.
	 *
	 * @param EventManager  $eventManager
	 * @param CacheProvider $cache
	 * @param Repository    $config
	 */
	private function addEventListeners(EventManager $eventManager, CacheProvider $cache, Repository $config)
	{
		$eventManager->addEventListener(Events::onFlush, new SoftDeletableListener);
		$eventManager->addEventListener(DBALEvents::postConnect, new DBVersionPersister($cache));
		$eventManager->addEventListener(DBALEvents::postConnect, Types\TypeExtender::instance());

		if ($config->get('app.debug') && isset($this->app['debugbar'])){
			$eventManager->addEventListener(
				EntityManagerCreating::class,
				new DebugLogging($this->app['debugbar'])
			);
		}
	}
}
