<?php namespace Digbang\Doctrine\Commands\Schema;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Illuminate\Console\Command;
use Illuminate\Contracts\Config\Repository;

abstract class AbstractSchemaCommand extends Command
{
    /**
     * @param SchemaTool $schemaTool
     * @param array      $metadatas
     * @param Repository $config
     *
     * @return mixed
     */
    abstract protected function executeSchemaCommand(SchemaTool $schemaTool, array $metadatas, Repository $config);

    final public function handle(EntityManagerInterface $em, Repository $config)
    {
        $metadataFactory = $em->getMetadataFactory();
        $metadataFactory->setCacheDriver(null);

        $metadatas = $metadataFactory->getAllMetadata();

        if ( ! empty($metadatas)) {
            // Create SchemaTool
            $tool = new SchemaTool($em);

            return $this->executeSchemaCommand($tool, $metadatas, $config);
        } else {
            $this->line('No Metadata Classes to process.');
            return 0;
        }
    }
}
