<?php namespace Digbang\Doctrine\Commands\Exec;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

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
class ImportCommand extends Command
{
    protected $name = 'doctrine:exec:file';

    protected $description = 'Executes SQL file(s) directly to Database.';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setHelp('Import SQL file(s) directly to Database.');
    }

    protected function getArguments()
    {
        return [
            ['file', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'File path(s) of SQL to be executed.']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(EntityManagerInterface $em)
    {
        $conn = $em->getConnection();

        if (($fileNames = $this->argument('file')) !== null) {
            foreach ((array) $fileNames as $fileName) {
                $filePath = realpath($fileName);

                // Phar compatibility.
                if (false === $filePath) {
                    $filePath = $fileName;
                }

                if ( ! file_exists($filePath)) {
                    throw new \InvalidArgumentException(
                        sprintf("SQL file '<info>%s</info>' does not exist.", $filePath)
                    );
                } elseif ( ! is_readable($filePath)) {
                    throw new \InvalidArgumentException(
                        sprintf("SQL file '<info>%s</info>' does not have read permissions.", $filePath)
                    );
                }

                $this->line(sprintf("Processing file '<info>%s</info>'... ", $filePath));
                $sql = file_get_contents($filePath);

                if ($conn instanceof \Doctrine\DBAL\Driver\PDOConnection) {
                    // PDO Drivers
                    try {
                        $lines = 0;

                        $stmt = $conn->prepare($sql);
                        $stmt->execute();

                        do {
                            // Required due to "MySQL has gone away!" issue
                            $stmt->fetch();
                            $stmt->closeCursor();

                            $lines++;
                        } while ($stmt->nextRowset());

                        $this->line(sprintf('%d statements executed!', $lines) . PHP_EOL);
                    } catch (\PDOException $e) {
                        $this->line('error!' . PHP_EOL);

                        throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
                    }
                } else {
                    // Non-PDO Drivers (ie. OCI8 driver)
                    $stmt = $conn->prepare($sql);
                    $rs = $stmt->execute();

                    if ($rs) {
                        $this->line('OK!' . PHP_EOL);
                    } else {
                        $error = $stmt->errorInfo();

                        $this->line('error!' . PHP_EOL);

                        throw new \RuntimeException($error[2], $error[0]);
                    }

                    $stmt->closeCursor();
                }
            }
        }
    }
}
