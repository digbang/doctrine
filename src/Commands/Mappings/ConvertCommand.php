<?php namespace Digbang\Doctrine\Commands\Mappings;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\Export\ClassMetadataExporter;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Doctrine\ORM\Mapping\Driver\DatabaseDriver;
use Illuminate\Console\Command;

/**
 * Command to convert your mapping information between the various formats.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class ConvertCommand extends Command
{
    protected $name = 'doctrine:mappings:convert';

    protected $description = 'Convert mapping information between supported formats.';

    /**
     * @type EntityManagerInterface
     */
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setHelp(<<<EOT
Convert mapping information between supported formats.

This is an execute <info>one-time</info> command. It should not be necessary for
you to call this method multiple times, especially when using the <comment>--from-database</comment>
flag.

Converting an existing database schema into mapping files only solves about 70-80%
of the necessary mapping information. Additionally the detection from an existing
database cannot detect inverse associations, inheritance types,
entities with foreign keys as primary keys and many of the
semantical operations on associations such as cascade.

<comment>Hint:</comment> There is no need to convert YAML or XML mapping files to annotations
every time you make changes. All mapping drivers are first class citizens
in Doctrine 2 and can be used as runtime mapping for the ORM.

<comment>Hint:</comment> If you have a database with tables that should not be managed
by the ORM, you can use a DBAL functionality to filter the tables and sequences down
on a global level:

    \$config->setFilterSchemaAssetsExpression(\$regexp);
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $em = $this->em;

        if ($this->option('from-database') === true) {
            $databaseDriver = new DatabaseDriver(
                $em->getConnection()->getSchemaManager()
            );

            $em->getConfiguration()->setMetadataDriverImpl(
                $databaseDriver
            );

            if (($namespace = $this->option('namespace')) !== null) {
                $databaseDriver->setNamespace($namespace);
            }
        }

        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($em);
        $metadata = $cmf->getAllMetadata();
        $metadata = MetadataFilter::filter($metadata, $this->option('filter'));

        // Process destination directory
        if ( ! is_dir($destPath = $this->argument('dest-path'))) {
            mkdir($destPath, 0777, true);
        }
        $destPath = realpath($destPath);

        if ( ! file_exists($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Mapping destination directory '<info>%s</info>' does not exist.", $this->argument('dest-path'))
            );
        }

        if ( ! is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Mapping destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        $toType = strtolower($this->argument('to-type'));

        $exporter = $this->getExporter($toType, $destPath);
        $exporter->setOverwriteExistingFiles($this->option('force'));

        if ($toType == 'annotation') {
            $entityGenerator = new EntityGenerator();
            $exporter->setEntityGenerator($entityGenerator);

            $entityGenerator->setNumSpaces($this->option('num-spaces'));

            if (($extend = $this->option('extend')) !== null) {
                $entityGenerator->setClassToExtend($extend);
            }
        }

        if (count($metadata)) {
            foreach ($metadata as $class) {
                $this->line(sprintf('Processing entity "<info>%s</info>"', $class->name));
            }

            $exporter->setMetadata($metadata);
            $exporter->export();

            $this->line(PHP_EOL . sprintf(
                'Exporting "<info>%s</info>" mapping information to "<info>%s</info>"', $toType, $destPath
            ));
        } else {
            $this->line('No Metadata Classes to process.');
        }
    }

    /**
     * @param string $toType
     * @param string $destPath
     *
     * @return \Doctrine\ORM\Tools\Export\Driver\AbstractExporter
     */
    protected function getExporter($toType, $destPath)
    {
        $cme = new ClassMetadataExporter();

        return $cme->getExporter($toType, $destPath);
    }

    protected function getOptions()
    {
        return [
            [
                'filter', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'A string pattern used to match entities that should be processed.'],
            [
                'force', 'f', InputOption::VALUE_NONE,
                'Force to overwrite existing mapping files.'
            ],
            [
                'from-database', null, null, 'Whether or not to convert mapping information from existing database.'
            ],
            [
                'extend', null, InputOption::VALUE_OPTIONAL,
                'Defines a base class to be extended by generated entity classes.'
            ],
            [
                'num-spaces', null, InputOption::VALUE_OPTIONAL,
                'Defines the number of indentation spaces', 4
            ],
            [
                'namespace', null, InputOption::VALUE_OPTIONAL,
                'Defines a namespace for the generated entity classes, if converted from database.'
            ]
        ];
    }

    protected function getArguments()
    {
        return [
            [
                'to-type', InputArgument::REQUIRED,
                'The mapping type to be converted. One of: ' .
                'xml, yaml (or "yml"), php or annotation'
            ],
            [
                'dest-path', InputArgument::REQUIRED,
                'The path to generate your entities classes.'
            ],
        ];
    }
}
