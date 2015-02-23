<?php namespace Digbang\Doctrine\Commands\Schema;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Cache\Repository;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Tools\SchemaTool;

/**
 * Command to create the database schema for a set of classes based on their mappings.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class CreateCommand extends AbstractSchemaCommand
{
    protected $name = 'doctrine:schema:create';

    protected $description = 'Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output.';

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
Processes the schema and either create it directly on EntityManager Storage Connection or generate the SQL output.

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
                'Instead of trying to apply generated SQLs into EntityManager Storage Connection, output them.'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function executeSchemaCommand(SchemaTool $schemaTool, array $metadatas)
    {
        if ($this->option('dump-sql')) {
            $sqls = $schemaTool->getCreateSchemaSql($metadatas);
            $this->line(implode(';' . PHP_EOL, $sqls) . ';');
        } else {
            if (! $this->config->get('app.debug'))
            {
                $this->line('ATTENTION: This operation should not be executed in a production environment.' . PHP_EOL);
            }

            $this->line('Creating database schema...');
            $schemaTool->createSchema($metadatas);
            $this->line('Database schema created successfully!');
        }

        return 0;
    }
}
