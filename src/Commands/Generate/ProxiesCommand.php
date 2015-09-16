<?php namespace Digbang\Doctrine\Commands\Generate;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Tools\Console\MetadataFilter;
use Illuminate\Console\Command;

/**
 * Command to (re)generate the proxy classes used by doctrine.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class ProxiesCommand extends Command
{
    protected $name = 'doctrine:generate:proxies';

    protected $description = 'Generates proxy classes for entity classes.';

    protected function configure()
    {
        $this->setHelp('Generates proxy classes for entity classes.');
    }

    /**
     * {@inheritdoc}
     */
    public function handle(EntityManagerInterface $em)
    {
        $metadatas = $em->getMetadataFactory()->getAllMetadata();
        $metadatas = MetadataFilter::filter($metadatas, $this->option('filter'));

        // Process destination directory
        if (($destPath = $this->argument('dest-path')) === null) {
            $destPath = $em->getConfiguration()->getProxyDir();
        }

        if ( ! is_dir($destPath)) {
            mkdir($destPath, 0777, true);
        }

        $destPath = realpath($destPath);

        if ( ! file_exists($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Proxies destination directory '<info>%s</info>' does not exist.", $em->getConfiguration()->getProxyDir())
            );
        }

        if ( ! is_writable($destPath)) {
            throw new \InvalidArgumentException(
                sprintf("Proxies destination directory '<info>%s</info>' does not have write permissions.", $destPath)
            );
        }

        if ( count($metadatas)) {
            foreach ($metadatas as $metadata) {
                $this->line(
                    sprintf('Processing entity "<info>%s</info>"', $metadata->name)
                );
            }

            // Generating Proxies
            $em->getProxyFactory()->generateProxyClasses($metadatas, $destPath);

            // Outputting information message
            $this->line(PHP_EOL . sprintf('Proxy classes generated to "<info>%s</INFO>"', $destPath));
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
            ]
        ];
    }

    protected function getArguments()
    {
        return [
            [
                'dest-path', InputArgument::OPTIONAL,
                'The path to generate your proxy classes. If none is provided, it will attempt to grab from configuration.'
            ]
        ];
    }
}
