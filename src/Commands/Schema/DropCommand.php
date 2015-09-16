<?php namespace Digbang\Doctrine\Commands\Schema;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Config\Repository;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Command to drop the database schema for a set of classes based on their mappings.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class DropCommand extends AbstractSchemaCommand
{
	protected $name = 'doctrine:schema:drop';

	protected $description = 'Drop the complete database schema of EntityManager Storage Connection or generate the corresponding SQL output.';

	/**
	 * {@inheritdoc}
	 */
	protected function configure()
	{
		$this->setHelp(<<<EOT
Processes the schema and either drop the database schema of EntityManager Storage Connection or generate the SQL output.
Beware that the complete database is dropped by this command, even tables that are not relevant to your metadata model.

<comment>Hint:</comment> If you have a database with tables that should not be managed
by the ORM, you can use a DBAL functionality to filter the tables and sequences down
on a global level:

    \$config->setFilterSchemaAssetsExpression(\$regexp);
EOT
		);
	}

	protected function getOptions()
	{
		return [
			[
				'dump-sql', null, InputOption::VALUE_NONE,
				'Instead of trying to apply generated SQLs into EntityManager Storage Connection, output them.',
			],
			[
				'force', 'f', InputOption::VALUE_NONE,
				"Don't ask for the deletion of the database, but force the operation to run.",
			],
			[
				'full-database', null, InputOption::VALUE_NONE,
				'Instead of using the Class Metadata to detect the database table schema, drop ALL assets that the database contains.',
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	protected function executeSchemaCommand(SchemaTool $schemaTool, array $metadatas, Repository $config)
	{
		$isFullDatabaseDrop = $this->option('full-database');

		if ($this->option('dump-sql'))
		{
			if ($isFullDatabaseDrop)
			{
				$sqls = $schemaTool->getDropDatabaseSQL();
			}
			else
			{
				$sqls = $schemaTool->getDropSchemaSQL($metadatas);
			}
			$this->line(implode(';' . PHP_EOL, $sqls));

			return 0;
		}

		if ($this->option('force') || $config->get('app.debug'))
		{
			$this->line('Dropping database schema...');

			if ($isFullDatabaseDrop)
			{
				$schemaTool->dropDatabase();
			}
			else
			{
				$schemaTool->dropSchema($metadatas);
			}

			$this->line('Database schema dropped successfully!');

			return 0;
		}

		$this->line('<comment>ATTENTION</comment>: This operation should not be executed in a production environment.' . PHP_EOL);

		if ($isFullDatabaseDrop)
		{
			$sqls = $schemaTool->getDropDatabaseSQL();
		}
		else
		{
			$sqls = $schemaTool->getDropSchemaSQL($metadatas);
		}

		if (count($sqls))
		{
			$this->line(sprintf('The Schema-Tool would execute <info>%s</info> queries to update the database.', count($sqls)));
			$this->line('Please run the operation by passing one - or both - of the following options:');

			$this->line(sprintf('    <info>%s --force</info> to execute the command', $this->getName()));
			$this->line(sprintf('    <info>%s --dump-sql</info> to dump the SQL statements to the screen', $this->getName()));

			return 1;
		}

		$this->line('Nothing to drop. The database is empty!');

		return 0;
	}
}
