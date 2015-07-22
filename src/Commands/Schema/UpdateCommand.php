<?php namespace Digbang\Doctrine\Commands\Schema;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Contracts\Config\Repository;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Command to generate the SQL needed to update the database schema to match
 * the current mapping information.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 * @author  Ryan Weaver <ryan@thatsquality.com>
 */
class UpdateCommand extends AbstractSchemaCommand
{
    /**
     * @var string
     */
    protected $name = 'doctrine:schema:update';

    protected $description = 'Executes (or dumps) the SQL needed to update the database schema to match the current mapping metadata.';

    /**
     * @type Repository
     */
    private $config;

    function __construct(EntityManagerInterface $em, Repository $config)
    {
        parent::__construct($em);

        $this->config = $config;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>%command.name%</info> command generates the SQL needed to
synchronize the database schema with the current mapping metadata of the
default entity manager.

For example, if you add metadata for a new column to an entity, this command
would generate and output the SQL needed to add the new column to the database:

<info>%command.name% --dump-sql</info>

Alternatively, you can execute the generated queries:

<info>%command.name% --force</info>

If both options are specified, the queries are output and then executed:

<info>%command.name% --dump-sql --force</info>

Finally, be aware that if the <info>--complete</info> option is passed, this
task will drop all database assets (e.g. tables, etc) that are *not* described
by the current metadata. In other words, without this option, this task leaves
untouched any "extra" tables that exist in the database, but which aren't
described by any metadata.

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
                'complete', null, InputOption::VALUE_NONE,
                'If defined, all assets of the database which are not relevant to the current metadata will be dropped.'
            ],
            [
                'dump-sql', null, InputOption::VALUE_NONE,
                'Dumps the generated SQL statements to the screen (does not execute them).'
            ],
            [
                'force', 'f', InputOption::VALUE_NONE,
                'Causes the generated SQL statements to be physically executed against your database.'
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function executeSchemaCommand(SchemaTool $schemaTool, array $metadatas)
    {
        // Defining if update is complete or not (--complete not defined means $saveMode = true)
        $saveMode = ! $this->option('complete');

        $sqls = $schemaTool->getUpdateSchemaSql($metadatas, $saveMode);

        if (0 === count($sqls)) {
            $this->line('Nothing to update - your database is already in sync with the current entity metadata.');

            return 0;
        }

        $dumpSql = true === $this->option('dump-sql');
        $force   = (true === $this->option('force') || $this->config->get('app.debug'));

        if ($dumpSql) {
            $this->line(implode(';' . PHP_EOL, $sqls) . ';');
        }

        if ($force) {
        	if ($dumpSql) {
                $this->line('');
        	}
            $this->line('Updating database schema...');
            $schemaTool->updateSchema($metadatas, $saveMode);
            $this->line(sprintf('Database schema updated successfully! <info>%s</info> queries were executed', count($sqls)));
        }

        if ($dumpSql || $force) {
            return 0;
        }

        $this->line('<comment>ATTENTION</comment>: This operation should not be executed in a production environment.');
        $this->line('           Use the incremental update to detect changes during development and use');
        $this->line('           the SQL DDL provided to manually update your database in production.');
        $this->line('');

        $this->line(sprintf('The Schema-Tool would execute <info>%s</info> queries to update the database.', count($sqls)));
        $this->line('Please run the operation by passing one - or both - of the following options:');

        $this->line(sprintf('    <info>%s --force</info> to execute the command', $this->getName()));
        $this->line(sprintf('    <info>%s --dump-sql</info> to dump the SQL statements to the screen', $this->getName()));

        return 1;
    }
}
