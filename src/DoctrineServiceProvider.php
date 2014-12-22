<?php namespace Digbang\Doctrine;

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Digbang\Doctrine\Cache\Bridge;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\EntityManagerInterface;
use Digbang\Doctrine\Metadata\ConfigurationDriver;
use Digbang\Doctrine\Configuration\DatabaseConfigurationBridge;

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

		$this->registerShutdownHandler();
	}

	private function registerEntityManager()
	{
		// bind the EM interface to our only EM as a singleton
		$this->app->singleton(EntityManagerInterface::class, EntityManager::class);

		// bind the EM concrete (should I use a factory here?)
		$this->app->singleton(EntityManager::class, function(Container $app) {
			/** @type \Illuminate\Config\Repository $config */
			$config = $app['config'];

			$configuration = Setup::createConfiguration(
				$config->get('app.debug'),
				storage_path('proxies')
			);

			$driver = new ConfigurationDriver($config, $app);

			$configuration->setMetadataDriverImpl($driver);
			$configuration->setAutoGenerateProxyClasses(true);
			$configuration->setRepositoryFactory($app->make(RepositoryFactory::class));
			$configuration->setNamingStrategy($app->make(LaravelNamingStrategy::class));

			if ($config->get('doctrine::cache.enabled'))
			{
				/** @type Bridge $cacheBridge */
				$cacheBridge = $app->make(Bridge::class);

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

			return EntityManager::create($conn, $configuration);
		});
	}

	private function registerShutdownHandler()
	{
		// Flush EM if left open (?)
		$this->app->shutdown(function(\Illuminate\Container\Container $app){
			/** @type EntityManagerInterface $em */
			$em = $app->make(EntityManagerInterface::class);

			if ($em->isOpen())
			{
				$em->flush();
			}
		});
	}

	public function boot()
	{
		$this->package('digbang/doctrine', null, dirname(__DIR__));
	}
}
 