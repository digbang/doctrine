<?php namespace Digbang\Doctrine\Collectors;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\DataCollectorInterface;
use DebugBar\DataCollector\Renderable;
use Doctrine\ORM\Cache\Logging\StatisticsCacheLogger;

class CacheDataCollector extends DataCollector implements DataCollectorInterface, Renderable
{
	/**
	 * @type StatisticsCacheLogger
	 */
	private $cacheLogger;

	function __construct(StatisticsCacheLogger $cacheLogger)
	{
		$this->cacheLogger = $cacheLogger;
	}

	/**
	 * Called by the DebugBar when data needs to be collected
	 *
	 * @return array Collected data
	 */
	function collect()
	{
		return [
			'hits'   => $this->cacheLogger->getHitCount(),
			'misses' => $this->cacheLogger->getMissCount(),
			'puts'   => $this->cacheLogger->getPutCount()
		];
	}

	/**
	 * Returns the unique name of the collector
	 *
	 * @return string
	 */
	function getName()
	{
		return 'cache';
	}

	public function getWidgets()
	{
		return [
			'cache' => [
				'icon' => 'archive',
				'widget' => 'PhpDebugBar.Widgets.VariableListWidget',
				'tooltip' => 'Cache information',
				'map' => 'cache',
				'title' => 'Cache',
				'default' => '[]'
			],
			'cache:badge' => [
				'map' => 'cache.hits',
				"default" => 0
			]
		];
	}
}
