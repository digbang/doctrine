<?php namespace Digbang\Doctrine;

use Digbang\Doctrine\Bridges\CacheBridge;
use Digbang\Doctrine\Bridges\DatabaseConfigurationBridge;
use Digbang\Doctrine\Bridges\EventManagerBridge;
use Digbang\Doctrine\Listeners\SoftDeletableListener;
use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Setup;
use Illuminate\Config\Repository;
use Digbang\Doctrine\Filters\TrashedFilter;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;

class EntityManagerFactory
{
    /**
     * @type LazyLoadingValueHolderFactory
     */
    private $lazyLoadingValueHolderFactory;

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
	 * @param LazyLoadingValueHolderFactory $lazyLoadingValueHolderFactory
	 * @param Repository                    $config
	 * @param CacheBridge                   $cacheBridge
	 * @param DatabaseConfigurationBridge   $databaseConfigurationBridge
	 * @param RepositoryFactory             $repositoryFactory
	 * @param LaravelNamingStrategy         $laravelNamingStrategy
	 * @param DecoupledMappingDriver        $decoupledMappingDriver
	 * @param EventManagerBridge            $eventManagerBridge
	 */
    public function __construct(
	    LazyLoadingValueHolderFactory $lazyLoadingValueHolderFactory,
	    Repository                    $config,
	    CacheBridge                   $cacheBridge,
		DatabaseConfigurationBridge   $databaseConfigurationBridge,
		RepositoryFactory             $repositoryFactory,
		LaravelNamingStrategy         $laravelNamingStrategy,
		DecoupledMappingDriver        $decoupledMappingDriver,
		EventManagerBridge            $eventManagerBridge
    )
    {
        $this->lazyLoadingValueHolderFactory = $lazyLoadingValueHolderFactory;
        $this->config                        = $config;
        $this->cacheBridge                   = $cacheBridge;
	    $this->databaseConfigurationBridge   = $databaseConfigurationBridge;
	    $this->repositoryFactory             = $repositoryFactory;
	    $this->laravelNamingStrategy         = $laravelNamingStrategy;
	    $this->decoupledMappingDriver        = $decoupledMappingDriver;
	    $this->eventManagerBridge            = $eventManagerBridge;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function create(\DebugBar\DebugBar $debugBar = null)
    {
        return $this->lazyLoadingValueHolderFactory->createProxy(
            EntityManager::class,
            function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) use ($debugBar){
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

                $wrappedObject = $entityManager;
                $initializer = null;

	            return true;
            }
        );
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
            storage_path('proxies'),
            $this->cacheBridge
        );
        $configuration->setMetadataDriverImpl($this->decoupledMappingDriver);
        $configuration->setAutoGenerateProxyClasses(true);
        $configuration->setRepositoryFactory($this->repositoryFactory);
        $configuration->setNamingStrategy($this->laravelNamingStrategy);
        $configuration->addFilter('trashed', TrashedFilter::class);

        return $configuration;
    }
}
