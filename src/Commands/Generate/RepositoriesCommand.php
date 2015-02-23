<?php namespace Digbang\Doctrine\Commands\Generate;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Digbang\Doctrine\Tools\EntityRepositoryGenerator;
use Illuminate\Console\Command;

/**
 * Command to generate repository classes for mapping information.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class RepositoriesCommand extends Command
{
    protected $name = 'doctrine:generate:repositories';

    protected $description = 'Generate repository classes from your mapping information.';

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
        $this->setHelp('Generate repository classes from your mapping information.');
    }

    protected function getOptions()
    {
        return [
            [
                'filter', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'A string pattern used to match entities that should be processed.'
            ]
        ];
    }

    protected function getArguments()
    {
        return [
            ['dest-path', InputArgument::REQUIRED, 'The path to generate your repository classes.']
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $em = $this->em;

        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $metadatas = MetadataFilter::filter($metadatas, $this->option('filter'));

        $repositoryName = $em->getConfiguration()->getDefaultRepositoryClassName();

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
            $numRepositories = 0;
            $generator = new EntityRepositoryGenerator();

            $generator->setDefaultRepositoryName($repositoryName);

            foreach ($metadatas as $metadata) {
                /** @type ClassMetadata $metadata */
                if ($metadata->customRepositoryClassName) {
                    $this->line(
                        sprintf('Processing repository "<info>%s</info>"', $metadata->customRepositoryClassName)
                    );

                    $generator->writeEntityRepositoryClass($metadata->getName(),  $metadata->customRepositoryClassName, $destPath);

                    $numRepositories++;
                }
            }

            if ($numRepositories) {
                // Outputting information message
                $this->line(PHP_EOL . sprintf('Repository classes generated to "<info>%s</INFO>"', $destPath) );
            } else {
                $this->line('No Repository classes were found to be processed.' );
            }
        } else {
            $this->line('No Metadata Classes to process.' );
        }
    }
}
