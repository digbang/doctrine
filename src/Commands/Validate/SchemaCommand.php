<?php namespace Digbang\Doctrine\Commands\Validate;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\ORM\Tools\SchemaValidator;

/**
 * Command to validate that the current mapping is valid.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Benjamin Eberlei <kontakt@beberlei.de>
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Jonathan Wage <jonwage@gmail.com>
 * @author      Roman Borschel <roman@code-factory.org>
 */
class SchemaCommand extends Command
{
    protected $name = 'doctrine:validate:schema';

    protected $description = 'Validate the mapping files.';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setHelp('Validate that the mapping files are correct and in sync with the database.');
    }

    /**
     * {@inheritdoc}
     */
    public function handle(EntityManagerInterface $em)
    {
        $validator = new SchemaValidator($em);
        $exit = 0;

        if ($this->option('skip-mapping')) {
            $this->line('<comment>[Mapping]  Skipped mapping check.</comment>');
        } elseif ($errors = $validator->validateMapping()) {
            foreach ($errors as $className => $errorMessages) {
                $this->line("<error>[Mapping]  FAIL - The entity-class '" . $className . "' mapping is invalid:</error>");

                foreach ($errorMessages as $errorMessage) {
                    $this->line('* ' . $errorMessage);
                }

                $this->line('');
            }

            $exit += 1;
        } else {
            $this->line('<info>[Mapping]  OK - The mapping files are correct.</info>');
        }

        if ($this->option('skip-sync')) {
            $this->line('<comment>[Database] SKIPPED - The database was not checked for synchronicity.</comment>');
        } elseif (!$validator->schemaInSyncWithMetadata()) {
            $this->line('<error>[Database] FAIL - The database schema is not in sync with the current mapping file.</error>');
            $exit += 2;
        } else {
            $this->line('<info>[Database] OK - The database schema is in sync with the mapping files.</info>');
        }

        return $exit;
    }

    protected function getOptions()
    {
        return [
            [
                'skip-mapping',
                null,
                InputOption::VALUE_NONE,
                'Skip the mapping validation check'
            ],
            [
                'skip-sync',
                null,
                InputOption::VALUE_NONE,
                'Skip checking if the mapping is in sync with the database'
            ]
        ];
    }
}
