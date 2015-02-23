<?php namespace Digbang\Doctrine\Commands\Validate;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to ensure that Doctrine is properly configured for a production environment.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @version $Revision$
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class ProductionCommand extends Command
{
    protected $name = 'doctrine:validate:production';

    protected $description = 'Verify that Doctrine is properly configured for a production environment.';

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
        $this->setHelp('Verify that Doctrine is properly configured for a production environment.');
    }

    protected function getOptions()
    {
        return [
            [
                'complete', null, InputOption::VALUE_NONE,
                'Flag to also inspect database connection existence.'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $em = $this->em;

        try {
            $em->getConfiguration()->ensureProductionSettings();

            if ($this->option('complete') !== null) {
                $em->getConnection()->connect();
            }
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return 1;
        }

        $this->info('Environment is correctly configured for production.');

        return 0;
    }
}
