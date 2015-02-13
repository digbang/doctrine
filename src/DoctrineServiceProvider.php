<?php namespace Digbang\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Digbang\Doctrine\Cache\Bridge;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Digbang\Doctrine\Metadata\ConfigurationDriver;
use Digbang\Doctrine\Configuration\DatabaseConfigurationBridge;
use Mitch\LaravelDoctrine\Console;
use Mitch\LaravelDoctrine\EventListeners\SoftDeletableListener;
use Mitch\LaravelDoctrine\Filters\TrashedFilter;

class DoctrineServiceProvider extends ServiceProvider
{
	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerEntityManager();
		$this->registerClassMetadataFactory();
	}

	private function registerEntityManager()
	{
		// bind the EM interface to our only EM as a singleton
		$this->app->singleton(EntityManagerInterface::class, EntityManager::class);

		// bind the EM concrete
		$this->app->singleton(EntityManager::class, function(Container $app) {
			/** @type \Illuminate\Config\Repository $config */
			$config = $app['config'];

			/** @type Bridge $cacheBridge */
			$cacheBridge = $app->make(Bridge::class);

			$configuration = Setup::createConfiguration(
				$config->get('app.debug'),
				storage_path('proxies'),
				$cacheBridge
			);

			$driver = new ConfigurationDriver($config, $app);

			$configuration->setMetadataDriverImpl($driver);
			$configuration->setAutoGenerateProxyClasses(true);
			$configuration->setRepositoryFactory($app->make(RepositoryFactory::class));
			$configuration->setNamingStrategy($app->make(LaravelNamingStrategy::class));
            $configuration->addFilter('trashed', TrashedFilter::class);

			if ($config->get('doctrine::cache.enabled'))
			{
				if ($config->get('doctrine::cache.hydration'))
				{
					$configuration->setHydrationCacheImpl($cacheBridge);
				}

				if ($config->get('doctrine::cache.query'))
				{
					$configuration->setQueryCacheImpl($cacheBridge);
				}

				if ($config->get('doctrine::cache.result'))
				{
					$configuration->setResultCacheImpl($cacheBridge);
				}

				if ($config->get('doctrine::cache.metadata'))
				{
					$configuration->setMetadataCacheImpl($cacheBridge);
				}
			}

			$conn = $app->make(DatabaseConfigurationBridge::class)->getConnection();

			if (isset($app['debugbar']))
			{
				$debugStack = new \Doctrine\DBAL\Logging\DebugStack();
				$configuration->setSQLLogger($debugStack);

				/** @type \DebugBar\DebugBar $debugbar */
				$debugbar = $app['debugbar'];
				$debugbar->addCollector(new \DebugBar\Bridge\DoctrineCollector($debugStack));
			}

            $eventManager = new EventManager;
            $eventManager->addEventListener(Events::onFlush, new SoftDeletableListener);

            $entityManager = EntityManager::create($conn, $configuration, $eventManager);
            $entityManager->getFilters()->enable('trashed');

            return $entityManager;
		});
	}

	private function registerClassMetadataFactory()
	{
		$this->app->singleton(ClassMetadataFactory::class, function ($app) {
			return $app[EntityManager::class]->getMetadataFactory();
		});
	}

	public function boot()
	{
		$this->package('digbang/doctrine', null, realpath(__DIR__));

		$this->commands([
			Console\GenerateProxiesCommand::class,
			Console\SchemaCreateCommand::class,
			Console\SchemaUpdateCommand::class,
			Console\SchemaDropCommand::class
		]);
	}
}
 