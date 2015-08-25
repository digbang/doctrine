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
     * The application instance.
     *
     * @type \Illuminate\Container\Container
     */
    protected $app;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
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
		$this->package('digbang/doctrine', null, realpath(__DIR__));

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
	    Types\TypeExtender::instance()
		    ->add(Types\CarbonType::DATETIMETZ, 'TIMESTAMP(0) WITH TIME ZONE',    Types\CarbonDateTimeTzType::class)
			->add(Types\CarbonType::DATETIME,   'TIMESTAMP(0) WITHOUT TIME ZONE', Types\CarbonDateTimeType::class)
			->add(Types\CarbonType::DATE,       'DATE',                           Types\CarbonDateType::class)
			->add(Types\CarbonType::TIME,       'TIME(0) WITHOUT TIME ZONE',      Types\CarbonTimeType::class)
			->add(Types\TsvectorType::TSVECTOR, 'TSVECTOR',                       Types\TsvectorType::class);
    }
}
