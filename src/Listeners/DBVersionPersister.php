<?php
namespace Digbang\Doctrine\Listeners;

use Doctrine\Common\Cache\CacheProvider;
use Doctrine\DBAL\Driver\ServerInfoAwareConnection;
use Doctrine\DBAL\Event\ConnectionEventArgs;

class DBVersionPersister
{
	const CACHE_KEY = 'database.version';

	/**
	 * @type CacheProvider
	 */
	private $cache;

	/**
	 * DatabaseVersionPersister constructor.
	 *
	 * @param CacheProvider $cache
	 */
	public function __construct(CacheProvider $cache)
	{
		$this->cache = $cache;
	}

	/**
	 * Save the db server version to cache on each connect.
	 *
	 * @param ConnectionEventArgs $args
	 */
	public function postConnect(ConnectionEventArgs $args)
	{
		$connection = $args->getConnection();

		if ($connection instanceof ServerInfoAwareConnection)
		{
			$this->cache->save(self::CACHE_KEY, $connection->getServerVersion());
		}
	}
}
