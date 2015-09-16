<?php namespace Digbang\Doctrine\Commands\Exec;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * Task for executing arbitrary SQL that can come from a file or directly from
 * the command line.
 *
 * @link   www.doctrine-project.org
 * @since  2.0
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @author Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author Jonathan Wage <jonwage@gmail.com>
 * @author Roman Borschel <roman@code-factory.org>
 */
class RunSqlCommand extends Command
{
    protected $name = 'doctrine:exec:sql';

    protected $description = 'Executes arbitrary SQL directly from the command line.';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setHelp('Executes arbitrary SQL directly from the command line.');
    }

    protected function getOptions()
    {
        return [
            ['depth', null, InputOption::VALUE_REQUIRED, 'Dumping depth of result set.', 7]
        ];
    }

    protected function getArguments()
    {
        return [
            ['sql', InputArgument::REQUIRED, 'The SQL statement to execute.']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(EntityManagerInterface $em)
    {
        $conn = $em->getConnection();

        if (($sql = $this->argument('sql')) === null) {
            throw new \RuntimeException("Argument 'SQL' is required in order to execute this command correctly.");
        }

        $depth = $this->option('depth');

        if ( ! is_numeric($depth)) {
            throw new \LogicException("Option 'depth' must contains an integer value");
        }

        if (stripos($sql, 'select') === 0) {
            $resultSet = $conn->fetchAll($sql);
        } else {
            $resultSet = $conn->executeUpdate($sql);
        }

        ob_start();
        \Doctrine\Common\Util\Debug::dump($resultSet, (int) $depth);
        $message = ob_get_clean();

        $this->line($message);
    }
}
