<?php namespace Digbang\Doctrine;

use Digbang\Doctrine\Bridges\DatabaseConfigurationBridge;
use Digbang\Doctrine\Events\EntityManagerCreated;
use Digbang\Doctrine\Events\EntityManagerCreating;
use Digbang\Doctrine\Filters\TrashedFilter;
use Digbang\Doctrine\Laravel\LaravelNamingStrategy;
use Digbang\Doctrine\Query\AST\Functions\PlainTsqueryFunction;
use Digbang\Doctrine\Query\AST\Functions\PlainTsrankFunction;
use Digbang\Doctrine\Query\AST\Functions\TsqueryFunction;
use Digbang\Doctrine\Query\AST\Functions\TsrankFunction;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\Cache\RegionsConfiguration;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
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
	 * @type CacheProvider
	 */
	private $cache;

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
	 * @type EventManager
	 */
	private $eventManager;

	/**
	 * @param Repository                  $config
	 * @param CacheProvider               $cache
	 * @param DatabaseConfigurationBridge $databaseConfigurationBridge
	 * @param NamingStrategy              $namingStrategy
	 * @param MappingDriver               $mappingDriver
	 * @param EventManager                $eventManager
	 */
	public function __construct(Repository $config, CacheProvider $cache, DatabaseConfigurationBridge $databaseConfigurationBridge, NamingStrategy $namingStrategy, MappingDriver $mappingDriver, EventManager $eventManager)
	{
		$this->config                      = $config;
		$this->cache                       = $cache;
		$this->databaseConfigurationBridge = $databaseConfigurationBridge;
		$this->namingStrategy              = $namingStrategy;
		$this->mappingDriver               = $mappingDriver;
		$this->eventManager                = $eventManager;
	}

	/**
	 * @return EntityManager
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function create()
	{
		$configuration = $this->createConfiguration();

		if ($this->config->get('doctrine-cache.enabled'))
		{
			$this->addCacheImplementation($configuration);
		}

		$conn = $this->databaseConfigurationBridge->getConnection();

		$this->fireCreatingEvent($conn, $configuration);

		$entityManager = EntityManager::create($conn, $configuration, $this->eventManager);
		$entityManager->getFilters()->enable('trashed');

		$this->fireCreatedEvent($entityManager);

		return $entityManager;
	}

	/**
	 * @param Configuration $configuration
	 */
	private function addCacheImplementation(Configuration $configuration)
	{
		if ($this->config->get('doctrine-cache.hydration'))
		{
			$configuration->setHydrationCacheImpl($this->cache);
		}

		if ($this->config->get('doctrine-cache.query'))
		{
			$configuration->setQueryCacheImpl($this->cache);
		}

		if ($this->config->get('doctrine-cache.result'))
		{
			$configuration->setResultCacheImpl($this->cache);
		}

		if ($this->config->get('doctrine-cache.metadata'))
		{
			$configuration->setMetadataCacheImpl($this->cache);
		}

		if ($this->config->get('doctrine-cache.entities'))
		{
			$configuration->setSecondLevelCacheEnabled();

			$cacheConfig = $configuration->getSecondLevelCacheConfiguration();

			$cacheFactory = new DefaultCacheFactory(
				new RegionsConfiguration,
				$this->cache
			);

			$cacheFactory->setFileLockRegionDirectory($this->config->get('doctrine.lock_files.directory'));

			$cacheConfig->setCacheFactory($cacheFactory);
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
			$this->config->get('doctrine.proxies.directory'),
			// Use ArrayCache by default (no cache), then override if configured.
			new ArrayCache()
		);
		$configuration->setMetadataDriverImpl($this->mappingDriver);
		$configuration->setAutoGenerateProxyClasses(
			$this->config->get('doctrine.proxies.autogenerate', true)
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
		$this->eventManager->dispatchEvent(
			EntityManagerCreating::class,
			new EntityManagerCreating(
				$conn, $configuration, $this->eventManager
			)
		);
	}

	/**
	 * @param EntityManager $entityManager
	 */
	private function fireCreatedEvent(EntityManager $entityManager)
	{
		$this->eventManager->dispatchEvent(
			EntityManagerCreated::class,
			new EntityManagerCreated($entityManager)
		);
	}
}
