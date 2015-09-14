<?php namespace Digbang\Doctrine;

use Digbang\Doctrine\Bridges\CacheBridge;
use Digbang\Doctrine\Bridges\DatabaseConfigurationBridge;
use Digbang\Doctrine\Bridges\EventManagerBridge;
use Digbang\Doctrine\Collectors\CacheDataCollector;
use Digbang\Doctrine\Events\EntityManagerCreated;
use Digbang\Doctrine\Events\EntityManagerCreating;
use Digbang\Doctrine\Filters\TrashedFilter;
use Digbang\Doctrine\Laravel\LaravelNamingStrategy;
use Digbang\Doctrine\Listeners\SoftDeletableListener;
use Digbang\Doctrine\Query\AST\Functions\PlainTsqueryFunction;
use Digbang\Doctrine\Query\AST\Functions\PlainTsrankFunction;
use Digbang\Doctrine\Query\AST\Functions\TsqueryFunction;
use Digbang\Doctrine\Query\AST\Functions\TsrankFunction;
use Digbang\Doctrine\Types\TypeExtender;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Events as DBALEvents;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\Cache\CacheConfiguration;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Cache\Logging\StatisticsCacheLogger;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\NamingStrategy;
use Doctrine\ORM\Tools\Setup;
use Illuminate\Contracts\Config\Repository;

class EntityManagerFactory
{
	/**
	 * @type Repository
	 */
	private $config;

	/**
	 * @type CacheBridge
	 */
	private $cacheBridge;

	/**
	 * @type DatabaseConfigurationBridge
	 */
	private $databaseConfigurationBridge;

	/**
	 * @type LaravelNamingStrategy
	 */
	private $namingStrategy;

	/**
	 * @type MappingDriver
	 */
	private $mappingDriver;

	/**
	 * @type EventManagerBridge
	 */
	private $eventManagerBridge;

	/**
	 * @param Repository                  $config
	 * @param CacheBridge                 $cacheBridge
	 * @param DatabaseConfigurationBridge $databaseConfigurationBridge
	 * @param NamingStrategy              $namingStrategy
	 * @param MappingDriver               $mappingDriver
	 * @param EventManagerBridge          $eventManagerBridge
	 */
	public function __construct(Repository $config, CacheBridge $cacheBridge, DatabaseConfigurationBridge $databaseConfigurationBridge, NamingStrategy $namingStrategy, MappingDriver $mappingDriver, EventManagerBridge $eventManagerBridge)
	{
		$this->config                      = $config;
		$this->cacheBridge                 = $cacheBridge;
		$this->databaseConfigurationBridge = $databaseConfigurationBridge;
		$this->namingStrategy              = $namingStrategy;
		$this->mappingDriver               = $mappingDriver;
		$this->eventManagerBridge          = $eventManagerBridge;
	}

	/**
	 * @param \DebugBar\DebugBar|null $debugBar
	 *
	 * @return EntityManager
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function create(\DebugBar\DebugBar $debugBar = null)
	{
		$configuration = $this->createConfiguration();

		if ($this->config->get('doctrine::cache.enabled'))
		{
			$this->addCacheImplementation($configuration, $debugBar);
		}

		$conn = $this->databaseConfigurationBridge->getConnection();

		if ($debugBar !== null)
		{
			$this->addSQLLogger($configuration, $debugBar);
		}

		$this->fireCreatingEvent($conn, $configuration);

		$entityManager = EntityManager::create($conn, $configuration, $this->eventManagerBridge);
		$entityManager->getFilters()->enable('trashed');

		$this->addEventListeners($this->eventManagerBridge);

		$this->fireCreatedEvent($entityManager);

		return $entityManager;
	}

	/**
	 * @param Configuration      $configuration
	 * @param \DebugBar\DebugBar $debugBar
	 *
	 * @throws \DebugBar\DebugBarException
	 */
	private function addSQLLogger(Configuration $configuration, \DebugBar\DebugBar $debugBar)
	{
		$debugStack = new DebugStack();
		$configuration->setSQLLogger($debugStack);
		$debugBar->addCollector(new \DebugBar\Bridge\DoctrineCollector($debugStack));
	}

	/**
	 * @param CacheConfiguration $configuration
	 * @param \DebugBar\DebugBar $debugBar
	 *
	 * @throws \DebugBar\DebugBarException
	 */
	private function addCacheLogger(CacheConfiguration $configuration, \DebugBar\DebugBar $debugBar)
	{
		$cacheLogger = new StatisticsCacheLogger();
		$configuration->setCacheLogger($cacheLogger);

		$debugBar->addCollector(new CacheDataCollector($cacheLogger));
	}

	/**
	 * @param Configuration      $configuration
	 * @param \DebugBar\DebugBar $debugBar
	 */
	private function addCacheImplementation(Configuration $configuration, \DebugBar\DebugBar $debugBar = null)
	{
		if ($this->config->get('doctrine::cache.hydration'))
		{
			$configuration->setHydrationCacheImpl($this->cacheBridge);
		}

		if ($this->config->get('doctrine::cache.query'))
		{
			$configuration->setQueryCacheImpl($this->cacheBridge);
		}

		if ($this->config->get('doctrine::cache.result'))
		{
			$configuration->setResultCacheImpl($this->cacheBridge);
		}

		if ($this->config->get('doctrine::cache.metadata'))
		{
			$configuration->setMetadataCacheImpl($this->cacheBridge);
		}

		if ($this->config->get('doctrine::cache.entities'))
		{
			$configuration->setSecondLevelCacheEnabled();

			$cacheConfig = $configuration->getSecondLevelCacheConfiguration();

			$cacheFactory = new DefaultCacheFactory(
				new RegionsConfiguration,
				$this->cacheBridge
			);
			$cacheFactory->setFileLockRegionDirectory($this->config->get('doctrine::doctrine.lock_files.directory'));

			$cacheConfig->setCacheFactory($cacheFactory);

			if ($debugBar)
			{
				$this->addCacheLogger($cacheConfig, $debugBar);
			}
		}
	}

	/**
	 * @return Configuration
	 * @throws \Doctrine\ORM\ORMException
	 */
	protected function createConfiguration()
	{
		$configuration = Setup::createConfiguration(
			$this->config->get('app.debug'),
			$this->config->get('doctrine::doctrine.proxies.directory'),
			// Use ArrayCache by default (no cache), then override if configured.
			new ArrayCache()
		);
		$configuration->setMetadataDriverImpl($this->mappingDriver);
		$configuration->setAutoGenerateProxyClasses(
			$this->config->get('doctrine::doctrine.proxies.autogenerate', true)
		);
		$configuration->setNamingStrategy($this->namingStrategy);
		$configuration->addFilter('trashed', TrashedFilter::class);

		$configuration->addCustomStringFunction(TsqueryFunction::TSQUERY,            TsqueryFunction::class);
		$configuration->addCustomStringFunction(PlainTsqueryFunction::PLAIN_TSQUERY, PlainTsqueryFunction::class);
		$configuration->addCustomStringFunction(TsrankFunction::TSRANK,              TsrankFunction::class);
		$configuration->addCustomStringFunction(PlainTsrankFunction::PLAIN_TSRANK,   PlainTsrankFunction::class);

		return $configuration;
	}

	/**
	 * @param $conn
	 * @param Configuration $configuration
	 */
	private function fireCreatingEvent($conn, Configuration $configuration)
	{
		$this->eventManagerBridge->dispatchEvent(
			EntityManagerCreating::class,
			new EntityManagerCreating(
				$conn, $configuration, $this->eventManagerBridge
			)
		);
	}

	/**
	 * @param EntityManager $entityManager
	 */
	private function fireCreatedEvent(EntityManager $entityManager)
	{
		$this->eventManagerBridge->dispatchEvent(
			EntityManagerCreated::class,
			new EntityManagerCreated($entityManager)
		);
	}

	/**
	 * @param EventManager $eventManager
	 */
	private function addEventListeners(EventManager $eventManager)
	{
		$eventManager->addEventListener(Events::onFlush, new SoftDeletableListener());

		$eventManager->addEventListener(DBALEvents::postConnect, function (ConnectionEventArgs $args) {
			$typeExtender = TypeExtender::instance();

			$typeExtender->apply();
			$typeExtender->register($args->getDatabasePlatform());

			$connection = $args->getConnection();
			if ($connection instanceof ServerInfoAwareConnection)
			{
				$this->cacheBridge->save('database.version', $connection->getServerVersion());
			}
		});
	}
}
