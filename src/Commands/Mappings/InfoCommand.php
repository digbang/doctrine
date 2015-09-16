<?php namespace Digbang\Doctrine\Commands\Mappings;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\MappingException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Illuminate\Console\Command;

/**
 * Show information about mapped entities.
 *
 * @link    www.doctrine-project.org
 * @since   2.1
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 */
class InfoCommand extends Command
{
    protected $name = 'doctrine:mappings';

    protected $description = 'Show basic information about all mapped entities';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>%command.name%</info> shows basic information about which
entities exist and possibly if their mapping information contains errors or
not.
EOT
        );
    }

    /**
     * {@inheritdoc}
     */
    public function handle(EntityManagerInterface $entityManager)
    {
        $entityClassNames = $entityManager->getConfiguration()
                                          ->getMetadataDriverImpl()
                                          ->getAllClassNames();

        if (!$entityClassNames) {
            throw new \Exception(
                'You do not have any mapped Doctrine ORM entities according to the current configuration. '.
                'If you have entities or mapping files you should check your mapping configuration for errors.'
            );
        }

        $this->line(sprintf("Found <info>%d</info> mapped entities:", count($entityClassNames)));

        $failure = false;

        foreach ($entityClassNames as $entityClassName) {
            try {
                $entityManager->getClassMetadata($entityClassName);
                $this->line(sprintf("<info>[OK]</info>   %s", $entityClassName));
            } catch (MappingException $e) {
                $this->line("<error>[FAIL]</error> ".$entityClassName);
                $this->line(sprintf("<comment>%s</comment>", $e->getMessage()));
                $this->line('');

                $failure = true;
            }
        }

        return $failure ? 1 : 0;
    }
}
