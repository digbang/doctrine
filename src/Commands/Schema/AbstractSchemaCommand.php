<?php namespace Digbang\Doctrine\Commands\Schema;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Illuminate\Console\Command;

abstract class AbstractSchemaCommand extends Command
{
    abstract protected function executeSchemaCommand(SchemaTool $schemaTool, array $metadatas);

    public function handle(EntityManagerInterface $em)
    {
        $metadataFactory = $em->getMetadataFactory();
        $metadataFactory->setCacheDriver(null);

        $metadatas = $metadataFactory->getAllMetadata();

        if ( ! empty($metadatas)) {
            // Create SchemaTool
            $tool = new SchemaTool($em);

            return $this->executeSchemaCommand($tool, $metadatas);
        } else {
            $this->line('No Metadata Classes to process.');
            return 0;
        }
    }
}
