<?php
namespace Digbang\Doctrine\Listeners;

use DebugBar\Bridge\DoctrineCollector;
use DebugBar\DebugBar;
use Digbang\Doctrine\Collectors\CacheDataCollector;
use Digbang\Doctrine\Events\EntityManagerCreating;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\ORM\Cache\CacheConfiguration;
use Doctrine\ORM\Cache\Logging\StatisticsCacheLogger;
use Doctrine\ORM\Configuration;

class DebugLogging
{
	/**
	 * @type DebugBar
	 */
	private $debugbar;

	/**
	 * DebugLogging constructor.
	 *
	 * @param DebugBar $debugbar
	 */
	public function __construct(DebugBar $debugbar)
	{
		$this->debugbar = $debugbar;
	}

	/**
	 * @param EntityManagerCreating $event
	 */
	public function addLoggers(EntityManagerCreating $event)
	{
		$configuration = $event->getConfiguration();

		$this->addSQLLogger($configuration, $this->debugbar);

		if ($configuration->isSecondLevelCacheEnabled())
		{
			$this->addCacheLogger(
				$configuration->getSecondLevelCacheConfiguration(),
				$this->debugbar
			);
		}
	}

	/**
	 * @param Configuration      $configuration
	 * @param DebugBar $debugBar
	 *
	 * @throws \DebugBar\DebugBarException
	 */
	private function addSQLLogger(Configuration $configuration, DebugBar $debugBar)
	{
		$debugStack = new DebugStack();
		$configuration->setSQLLogger($debugStack);
		$debugBar->addCollector(new DoctrineCollector($debugStack));
	}

	/**
	 * @param CacheConfiguration $configuration
	 * @param DebugBar $debugBar
	 *
	 * @throws \DebugBar\DebugBarException
	 */
	private function addCacheLogger(CacheConfiguration $configuration, DebugBar $debugBar)
	{
		$cacheLogger = new StatisticsCacheLogger();
		$configuration->setCacheLogger($cacheLogger);

		$debugBar->addCollector(new CacheDataCollector($cacheLogger));
	}
}
