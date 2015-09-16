<?php namespace Digbang\Doctrine\Commands\ClearCache;

use Doctrine\ORM\EntityManagerInterface;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\XcacheCache;

/**
 * Command to clear the metadata cache of the various cache drivers.
 *
 * @link    www.doctrine-project.org
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class MetadataCommand extends Command
{
    protected $name = 'doctrine:clear-cache:metadata';

    protected $description = 'Clear all metadata cache of the various cache drivers.';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setHelp(<<<EOT
The <info>%command.name%</info> command is meant to clear the metadata cache of associated Entity Manager.
It is possible to invalidate all cache entries at once - called delete -, or flushes the cache provider
instance completely.

The execution type differ on how you execute the command.
If you want to invalidate the entries (and not delete from cache instance), this command would do the work:

<info>%command.name%</info>

Alternatively, if you want to flush the cache provider using this command:

<info>%command.name% --flush</info>

Finally, be aware that if <info>--flush</info> option is passed, not all cache providers are able to flush entries,
because of a limitation of its execution nature.
EOT
        );
    }

    protected function getOptions()
    {
        return [
            [
                'flush', null, InputOption::VALUE_NONE,
                'If defined, cache entries will be flushed instead of deleted/invalidated.'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function handle(EntityManagerInterface $em)
    {
        $cacheDriver = $em->getConfiguration()->getMetadataCacheImpl();

        if ( ! $cacheDriver) {
            throw new \InvalidArgumentException('No Metadata cache driver is configured on given EntityManager.');
        }

        if ($cacheDriver instanceof ApcCache) {
            throw new \LogicException("Cannot clear APC Cache from Console, its shared in the Webserver memory and not accessible from the CLI.");
        }

        if ($cacheDriver instanceof XcacheCache) {
            throw new \LogicException("Cannot clear XCache Cache from Console, its shared in the Webserver memory and not accessible from the CLI.");
        }


        $this->line('Clearing ALL Metadata cache entries');

        $result  = $cacheDriver->deleteAll();
        $message = ($result) ? 'Successfully deleted cache entries.' : 'No cache entries were deleted.';

        if (true === $this->option('flush')) {
            $result  = $cacheDriver->flushAll();
            $message = ($result) ? 'Successfully flushed cache entries.' : $message;
        }

        $this->line($message);
    }
}
