<?php namespace Digbang\Doctrine;

use Digbang\Doctrine\Cache\Bridge;
use Digbang\Doctrine\Configuration\DatabaseConfigurationBridge;
use Digbang\Doctrine\Listeners\SoftDeletableListener;
use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Setup;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Mitch\LaravelDoctrine\Filters\TrashedFilter;
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
     * @type Bridge
     */
    private $cacheBridge;

    /**
     * @param LazyLoadingValueHolderFactory $lazyLoadingValueHolderFactory
     */
    public function __construct(LazyLoadingValueHolderFactory $lazyLoadingValueHolderFactory, Repository $config, Bridge $cacheBridge)
    {
        $this->lazyLoadingValueHolderFactory = $lazyLoadingValueHolderFactory;
        $this->config = $config;
        $this->cacheBridge = $cacheBridge;
    }

    /**
     * @param Container $app
     *
     * @return \Doctrine\ORM\EntityManager
     */
    public function create(Container $app)
    {
        return $this->lazyLoadingValueHolderFactory->createProxy(
            EntityManager::class,
            function (& $wrappedObject, $proxy, $method, $parameters, & $initializer) use ($app) {
                /** @type \Doctrine\ORM\Configuration $configuration */
                $driver = new DecoupledMappingDriver($this->config, $app);

                $configuration = $this->createConfiguration($driver, $app);

                if ($this->config->get('doctrine::cache.enabled'))
                {
                    $this->addCacheImplementation($configuration);
                }

                $conn = $app->make(DatabaseConfigurationBridge::class)->getConnection();

                if (isset($app['debugbar']))
                {
                    $this->addLogger($app['debugbar'], $configuration);
                }

                $eventManager = new EventManager();
                $eventManager->addEventListener(Events::onFlush, new SoftDeletableListener());

                $entityManager = EntityManager::create($conn, $configuration, $eventManager);
                $entityManager->getFilters()->enable('trashed');

                $wrappedObject = $entityManager;
                $initializer = null;
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

    protected function createConfiguration(MappingDriver $driver, Container $app)
    {
        $configuration = Setup::createConfiguration(
            $this->config->get('app.debug'),
            storage_path('proxies'),
            $this->cacheBridge
        );
        $configuration->setMetadataDriverImpl($driver);
        $configuration->setAutoGenerateProxyClasses(true);
        $configuration->setRepositoryFactory($app->make(RepositoryFactory::class));
        $configuration->setNamingStrategy($app->make(LaravelNamingStrategy::class));
        $configuration->addFilter('trashed', TrashedFilter::class);

        return $configuration;
    }
}
