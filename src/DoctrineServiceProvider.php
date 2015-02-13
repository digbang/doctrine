<?php namespace Digbang\Doctrine;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Events;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Digbang\Doctrine\Cache\Bridge;
use Illuminate\Auth\AuthManager;
use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Digbang\Doctrine\Configuration\DatabaseConfigurationBridge;
use Mitch\LaravelDoctrine\Console;
use Mitch\LaravelDoctrine\DoctrineUserProvider;
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
            return $app->make(EntityManagerFactory::class)->create($app);
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

        $this->registerAuthDriver();

		$this->commands([
			Console\GenerateProxiesCommand::class,
			Console\SchemaCreateCommand::class,
			Console\SchemaUpdateCommand::class,
			Console\SchemaDropCommand::class
		]);
	}

    private function registerAuthDriver()
    {
        if (isset($this->app[AuthManager::class]))
        {
            $this->app[AuthManager::class]->extend('doctrine', function ($app) {
                return new DoctrineUserProvider(
                    $app['Illuminate\Hashing\HasherInterface'],
                    $app[EntityManager::class],
                    $app['config']['auth.model']
                );
            });
        }
    }
}
 