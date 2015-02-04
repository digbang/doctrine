<?php namespace Digbang\Doctrine;

use Digbang\Doctrine\Types;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
use Illuminate\Container\Container;
use Illuminate\Hashing\HasherInterface;
use Illuminate\Support\ServiceProvider;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Mitch\LaravelDoctrine\Console;

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
		$this->mergeConfigFrom(__DIR__ . '/config/repositories.php', 'doctrine-repositories');

		$this->registerEntityManager();
		$this->registerClassMetadataFactory();
        $this->registerTypes();
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

	private function registerClassMetadataFactory()
	{
		$this->app->singleton(ClassMetadataFactory::class, function ($app) {
			return $app[EntityManager::class]->getMetadataFactory();
		});
	}

	public function boot()
	{
		$configPath = $this->app->make('path.config');
		$this->publishes([
			__DIR__ . '/config/cache.php' => $configPath . '/doctrine-cache.php',
			__DIR__ . '/config/doctrine.php' => $configPath . '/doctrine.php',
			__DIR__ . '/config/mappings.php' => $configPath . '/doctrine-mappings.php',
			__DIR__ . '/config/repositories.php' => $configPath . '/doctrine-repositories.php',
		], 'config');

        $this->registerAuthDriver();

		$this->commands([
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

            // doctrine:migrations
            Commands\Migrations\DiffCommand::class,
            Commands\Migrations\ExecuteCommand::class,
            Commands\Migrations\GenerateCommand::class,
            Commands\Migrations\MigrateCommand::class,
            Commands\Migrations\StatusCommand::class,
            Commands\Migrations\VersionCommand::class
		]);
	}

    private function registerAuthDriver()
    {
        $this->app['auth']->extend('doctrine', function ($app) {
            return new DoctrineUserProvider(
                $app[HasherInterface::class],
                $app[EntityManager::class],
                $app['config']['auth.model']
            );
        });
    }

    private function registerTypes()
    {
	    foreach (
		    [
			    Types\CarbonType::DATETIMETZ => Types\CarbonDateTimeTzType::class,
				Types\CarbonType::DATETIME   => Types\CarbonDateTimeType::class,
				Types\CarbonType::DATE       => Types\CarbonDateType::class,
				Types\CarbonType::TIME       => Types\CarbonTimeType::class,
				Types\TsvectorType::TSVECTOR => Types\TsvectorType::class,
		    ]
		    as $type => $class)
	    {
		    if (! Type::hasType($type))
		    {
			    Type::addType($type, $class);
		    }
	    }
    }
}
