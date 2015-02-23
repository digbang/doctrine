<?php namespace Digbang\Doctrine\Commands\Schema;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Illuminate\Console\Command;

abstract class AbstractSchemaCommand extends Command
{
    protected $em;

    function __construct(EntityManagerInterface $em)
    {
        parent::__construct();

        $this->em = $em;
    }

    abstract protected function executeSchemaCommand(SchemaTool $schemaTool, array $metadatas);

    public function fire()
    {
        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->em;

        $metadatas = $em->getMetadataFactory()->getAllMetadata();

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
