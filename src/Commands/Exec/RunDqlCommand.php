<?php namespace Digbang\Doctrine\Commands\Exec;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\Common\Util\Debug;

/**
 * Command to execute DQL queries in a given EntityManager.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class RunDqlCommand extends Command
{
    protected $name = 'doctrine:exec:dql';

    protected $description = 'Executes arbitrary DQL directly from the command line.';

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
        $this->setHelp('Executes arbitrary DQL directly from the command line.');
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        /* @var $em \Doctrine\ORM\EntityManagerInterface */
        $em = $this->em;

        if (($dql = $this->argument('dql')) === null) {
            throw new \RuntimeException("Argument 'DQL' is required in order to execute this command correctly.");
        }

        $depth = $this->option('depth');

        if ( ! is_numeric($depth)) {
            throw new \LogicException("Option 'depth' must contains an integer value");
        }

        $hydrationModeName = $this->option('hydrate');
        $hydrationMode = 'Doctrine\ORM\Query::HYDRATE_' . strtoupper(str_replace('-', '_', $hydrationModeName));

        if ( ! defined($hydrationMode)) {
            throw new \RuntimeException(
                "Hydration mode '$hydrationModeName' does not exist. It should be either: object. array, scalar or single-scalar."
            );
        }

        $query = $em->createQuery($dql);

        if (($firstResult = $this->option('first-result')) !== null) {
            if ( ! is_numeric($firstResult)) {
                throw new \LogicException("Option 'first-result' must contains an integer value");
            }

            $query->setFirstResult((int) $firstResult);
        }

        if (($maxResult = $this->option('max-result')) !== null) {
            if ( ! is_numeric($maxResult)) {
                throw new \LogicException("Option 'max-result' must contains an integer value");
            }

            $query->setMaxResults((int) $maxResult);
        }

        if ($this->option('show-sql')) {
            $this->line(Debug::dump($query->getSQL(), 2, true, false));
            return;
        }

        $resultSet = $query->execute([], constant($hydrationMode));

        $this->line(Debug::dump($resultSet, $this->option('depth'), true, false));
    }

    protected function getOptions()
    {
        return [
            [
                'hydrate', null, InputOption::VALUE_REQUIRED,
                'Hydration mode of result set. Should be either: object, array, scalar or single-scalar.',
                'object'
            ],
            [
                'first-result', null, InputOption::VALUE_REQUIRED,
                'The first result in the result set.'
            ],
            [
                'max-result', null, InputOption::VALUE_REQUIRED,
                'The maximum number of results in the result set.'
            ],
            [
                'depth', null, InputOption::VALUE_REQUIRED,
                'Dumping depth of Entity graph.', 7
            ],
            [
                'show-sql', null, InputOption::VALUE_NONE,
                'Dump generated SQL instead of executing query'
            ]
        ];
    }

    protected function getArguments()
    {
        return [
            ['dql', InputArgument::REQUIRED, 'The DQL to execute.']
        ];
    }
}
