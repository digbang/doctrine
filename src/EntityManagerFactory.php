<?php namespace Digbang\Doctrine;

use Digbang\Doctrine\Bridges\CacheBridge;
use Digbang\Doctrine\Bridges\DatabaseConfigurationBridge;
use Digbang\Doctrine\Bridges\EventManagerBridge;
use Digbang\Doctrine\Listeners\SoftDeletableListener;
use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Doctrine\Query\AST\Functions\TsqueryFunction;
use Digbang\Doctrine\Query\AST\Functions\TsrankFunction;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Setup;
use Illuminate\Config\Repository;
use Digbang\Doctrine\Filters\TrashedFilter;

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
	 * @type \Digbang\Doctrine\RepositoryFactory
	 */
	private $repositoryFactory;

	/**
	 * @type \Digbang\Doctrine\LaravelNamingStrategy
	 */
	private $laravelNamingStrategy;

	/**
	 * @type \Digbang\Doctrine\Metadata\DecoupledMappingDriver
	 */
	private $decoupledMappingDriver;

	/**
	 * @type \Digbang\Doctrine\Bridges\EventManagerBridge
	 */
	private $eventManagerBridge;

	/**
	 * @param Repository                    $config
	 * @param CacheBridge                   $cacheBridge
	 * @param DatabaseConfigurationBridge   $databaseConfigurationBridge
	 * @param RepositoryFactory             $repositoryFactory
	 * @param LaravelNamingStrategy         $laravelNamingStrategy
	 * @param DecoupledMappingDriver        $decoupledMappingDriver
	 * @param EventManagerBridge            $eventManagerBridge
	 */
    public function __construct(
	    Repository                  $config,
	    CacheBridge                 $cacheBridge,
		DatabaseConfigurationBridge $databaseConfigurationBridge,
		RepositoryFactory           $repositoryFactory,
		LaravelNamingStrategy       $laravelNamingStrategy,
		DecoupledMappingDriver      $decoupledMappingDriver,
		EventManagerBridge          $eventManagerBridge
    )
    {
        $this->config                      = $config;
        $this->cacheBridge                 = $cacheBridge;
	    $this->databaseConfigurationBridge = $databaseConfigurationBridge;
	    $this->repositoryFactory           = $repositoryFactory;
	    $this->laravelNamingStrategy       = $laravelNamingStrategy;
	    $this->decoupledMappingDriver      = $decoupledMappingDriver;
	    $this->eventManagerBridge          = $eventManagerBridge;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function create(\DebugBar\DebugBar $debugBar = null)
    {
        $configuration = $this->createConfiguration();

        if ($this->config->get('doctrine::cache.enabled'))
        {
            $this->addCacheImplementation($configuration);
        }

        $conn = $this->databaseConfigurationBridge->getConnection();

        if ($debugBar !== null)
        {
            $this->addLogger($debugBar, $configuration);
        }

        $this->eventManagerBridge->addEventListener(Events::onFlush, new SoftDeletableListener());

        $entityManager = EntityManager::create($conn, $configuration, $this->eventManagerBridge);
        $entityManager->getFilters()->enable('trashed');

	    $entityManager->getConnection()->getDatabasePlatform()->registerDoctrineTypeMapping('TSVECTOR', Types\TsvectorType::TSVECTOR);

        return $entityManager;
    }

    /**
     * @param \DebugBar\DebugBar $debugBar
     * @param Configuration      $configuration
     *
     * @throws \DebugBar\DebugBarException
     */
    private function addLogger(\DebugBar\DebugBar $debugBar, Configuration $configuration)
    {
        $debugStack = new \Doctrine\DBAL\Logging\DebugStack();
        $configuration->setSQLLogger($debugStack);

        $debugBar->addCollector(new \DebugBar\Bridge\DoctrineCollector($debugStack));
    }

    /**
     * @param Configuration $configuration
     */
    private function addCacheImplementation(Configuration $configuration)
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
    }

    protected function createConfiguration()
    {
        $configuration = Setup::createConfiguration(
            $this->config->get('app.debug'),
            $this->config->get('doctrine::doctrine.proxies.directory'),
            $this->cacheBridge
        );
        $configuration->setMetadataDriverImpl($this->decoupledMappingDriver);
        $configuration->setAutoGenerateProxyClasses($this->config->get('doctrine::doctrine.proxies.autogenerate', true));
        $configuration->setRepositoryFactory($this->repositoryFactory);
        $configuration->setNamingStrategy($this->laravelNamingStrategy);
        $configuration->addFilter('trashed', TrashedFilter::class);
	    $configuration->addCustomStringFunction(TsqueryFunction::TSQUERY, TsqueryFunction::class);
	    $configuration->addCustomStringFunction(TsrankFunction::TSRANK, TsrankFunction::class);

        return $configuration;
    }
}
