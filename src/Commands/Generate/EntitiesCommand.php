<?php namespace Digbang\Doctrine\Commands\Generate;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Doctrine\ORM\Tools\EntityGenerator;
use Doctrine\ORM\Tools\DisconnectedClassMetadataFactory;
use Illuminate\Console\Command;

/**
 * Command to generate entity classes and method stubs from your mapping information.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class EntitiesCommand extends Command
{
    protected $name = 'doctrine:generate:entities';

    protected $description = 'Generate entity classes and method stubs from your mapping information.';
    
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
Generate entity classes and method stubs from your mapping information.

If you use the <comment>--update-entities</comment> or <comment>--regenerate-entities</comment> flags your existing
code gets overwritten. The EntityGenerator will only append new code to your
file and will not delete the old code. However this approach may still be prone
to error and we suggest you use code repositories such as GIT or SVN to make
backups of your code.

It makes sense to generate the entity code if you are using entities as Data
Access Objects only and don't put much additional logic on them. If you are
however putting much more logic on the entities you should refrain from using
the entity-generator and code your entities manually.

<error>Important:</error> Even if you specified Inheritance options in your
XML or YAML Mapping files the generator cannot generate the base and
child classes for you correctly, because it doesn't know which
class is supposed to extend which. You have to adjust the entity
code manually for inheritance to work!
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $em = $this->em;

        $cmf = new DisconnectedClassMetadataFactory();
        $cmf->setEntityManager($em);
        $metadatas = $cmf->getAllMetadata();
        $metadatas = MetadataFilter::filter($metadatas, $this->option('filter'));

        // Process destination directory
        $destPath = realpath($this->argument('dest-path'));

        if ( ! file_exists($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Entities destination directory '<info>%s</info>' does not exist.", $this->argument('dest-path'))
            );
        }

        if ( ! is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Entities destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        if (count($metadatas)) {
            // Create EntityGenerator
            $entityGenerator = new EntityGenerator();

            $entityGenerator->setGenerateAnnotations($this->option('generate-annotations'));
            $entityGenerator->setGenerateStubMethods($this->option('generate-methods'));
            $entityGenerator->setRegenerateEntityIfExists($this->option('regenerate-entities'));
            $entityGenerator->setUpdateEntityIfExists($this->option('update-entities'));
            $entityGenerator->setNumSpaces($this->option('num-spaces'));
            $entityGenerator->setBackupExisting(!$this->option('no-backup'));

            if (($extend = $this->option('extend')) !== null) {
                $entityGenerator->setClassToExtend($extend);
            }

            foreach ($metadatas as $metadata) {
                $this->line(
                    sprintf('Processing entity "<info>%s</info>"', $metadata->name)
                );
            }

            // Generating Entities
            $entityGenerator->generate($metadatas, $destPath);

            // Outputting information message
            $this->line(PHP_EOL . sprintf('Entity classes generated to "<info>%s</INFO>"', $destPath));
        } else {
            $this->line('No Metadata Classes to process.');
        }
    }
    
    protected function getOptions()
    {
        return [
            [
                'filter', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'A string pattern used to match entities that should be processed.'
            ],

            [
                'generate-annotations', null, InputOption::VALUE_OPTIONAL,
                'Flag to define if generator should generate annotation metadata on entities.', false
            ],
            [
                'generate-methods', null, InputOption::VALUE_OPTIONAL,
                'Flag to define if generator should generate stub methods on entities.', true
            ],
            [
                'regenerate-entities', null, InputOption::VALUE_OPTIONAL,
                'Flag to define if generator should regenerate entity if it exists.', false
            ],
            [
                'update-entities', null, InputOption::VALUE_OPTIONAL,
                'Flag to define if generator should only update entity if it exists.', true
            ],
            [
                'extend', null, InputOption::VALUE_REQUIRED,
                'Defines a base class to be extended by generated entity classes.'
            ],
            [
                'num-spaces', null, InputOption::VALUE_REQUIRED,
                'Defines the number of indentation spaces', 4
            ],
            [
                'no-backup', null, InputOption::VALUE_NONE,
                'Flag to define if generator should avoid backuping existing entity file if it exists.'
            ]
        ];
    }

    protected function getArguments()
    {
        return [
            [
                'dest-path', InputArgument::REQUIRED, 'The path to generate your entity classes.'
            ]
        ];
    }
}
