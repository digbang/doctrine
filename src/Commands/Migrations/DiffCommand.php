<?php namespace Digbang\Doctrine\Commands\Migrations;

use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\DBAL\Version as DbalVersion;
use Doctrine\DBAL\Migrations\Configuration\Configuration;

/**
 * Command for generate migration classes by comparing your current database schema
 * to your mapping information.
 *
 * @license http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Jonathan Wage <jonwage@gmail.com>
 */
class DiffCommand extends GenerateCommand
{
    protected $name = 'doctrine:migrations:diff';

    protected $description = 'Generate a migration by comparing your current database to your mapping information.';

    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>%command.name%</info> command generates a migration by comparing your current database to your mapping information:

    <info>%command.full_name%</info>

You can optionally specify a <comment>--editor-cmd</comment> option to open the generated file in your favorite editor:

    <info>%command.full_name% --editor-cmd=mate</info>
EOT
            );
    }

    public function fire()
    {
        $isDbalOld = (DbalVersion::compare('2.2.0') > 0);
        $configuration = $this->getMigrationConfiguration();

        $em = $this->em;
        $conn = $em->getConnection();
        $platform = $conn->getDatabasePlatform();
        $metadata = $em->getMetadataFactory()->getAllMetadata();

        if (empty($metadata)) {
            $this->error('No mapping information to process.');
            return;
        }

        if ($filterExpr = $this->option('filter-expression')) {
            if ($isDbalOld) {
                throw new \InvalidArgumentException('The "--filter-expression" option can only be used as of Doctrine DBAL 2.2');
            }

            $conn->getConfiguration()
                ->setFilterSchemaAssetsExpression($filterExpr);
        }

        $tool = new SchemaTool($em);

        $fromSchema = $conn->getSchemaManager()->createSchema();
        $toSchema = $tool->getSchemaFromMetadata($metadata);

        //Not using value from options, because filters can be set from config.yml
        if ( ! $isDbalOld && $filterExpr = $conn->getConfiguration()->getFilterSchemaAssetsExpression()) {
            $tableNames = $toSchema->getTableNames();
            foreach ($tableNames as $tableName) {
                $tableName = substr($tableName, strpos($tableName, '.') + 1);
                if (!preg_match($filterExpr, $tableName)) {
                    $toSchema->dropTable($tableName);
                }
            }
        }

        $up = $this->buildCodeFromSql($configuration, $fromSchema->getMigrateToSql($toSchema, $platform));
        $down = $this->buildCodeFromSql($configuration, $fromSchema->getMigrateFromSql($toSchema, $platform));

        if ( ! $up && ! $down) {
            $this->error('No changes detected in your mapping information.');
            return;
        }

        $version = date('YmdHis');
        $path = $this->generateMigration($configuration, $version, $up, $down);

        $this->line(sprintf('Generated new migration class to "<info>%s</info>" from schema differences.', $path));
    }

    private function buildCodeFromSql(Configuration $configuration, array $sql)
    {
        $currentPlatform = $configuration->getConnection()->getDatabasePlatform()->getName();
        $code = [
            "\$this->abortIf(\$this->connection->getDatabasePlatform()->getName() != \"$currentPlatform\");", "",
        ];
        foreach ($sql as $query) {
            if (strpos($query, $configuration->getMigrationsTableName()) !== false) {
                continue;
            }
            $code[] = "\$this->addSql(\"$query\");";
        }
        return implode("\n", $code);
    }

    protected function getOptions()
    {
        return array_merge(
            parent::getOptions(),
            [
                ['filter-expression', null, InputOption::VALUE_OPTIONAL, 'Tables which are filtered by Regular Expression.']
            ]
        );
    }
}
