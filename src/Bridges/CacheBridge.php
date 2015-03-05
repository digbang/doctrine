<?php namespace Digbang\Doctrine\Bridges;

use Illuminate\Cache\Repository;
use Doctrine\Common\Cache\CacheProvider;

class CacheBridge extends CacheProvider
{
	/**
	 * @type Repository
	 */
	private $laravelCache;

	function __construct(Repository $laravelCache)
	{
		$this->laravelCache = $laravelCache;
	}

	/**
	 * Fetches an entry from the cache.
	 *
	 * @param string $id The id of the cache entry to fetch.
	 *
	 * @return mixed The cached data or FALSE, if no cache entry exists for the given id.
	 */
	protected function doFetch($id)
	{
		if (($data = $this->laravelCache->get($id)) !== null)
		{
			return $data;
		}

		return false;
	}

	/**
	 * Tests if an entry exists in the cache.
	 *
	 * @param string $id The cache id of the entry to check for.
	 *
	 * @return boolean TRUE if a cache entry exists for the given cache id, FALSE otherwise.
	 */
	protected function doContains($id)
	{
		return $this->laravelCache->has($id);
	}

	/**
	 * Puts data into the cache.
	 *
	 * @param string $id       The cache id.
	 * @param mixed  $data     The cache entry/data.
	 * @param int    $lifeTime The cache lifetime.
	 *                         If != 0, sets a specific lifetime for this cache entry (0 => infinite lifeTime).
	 *
	 * @return boolean TRUE if the entry was successfully stored in the cache, FALSE otherwise.
	 */
	protected function doSave($id, $data, $lifeTime = 0)
	{
		$this->laravelCache->put($id, $data, $lifeTime / 60);

		return true;
	}

	/**
	 * Deletes a cache entry.
	 *
	 * @param string $id The cache id.
	 *
	 * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
	 */
	protected function doDelete($id)
	{
		$this->laravelCache->getStore()->forget($id);

		return true;
	}

	/**
	 * Retrieves cached information from the data store.
	 *
	 * The server's statistics array has the following values:
	 *
	 * - <b>hits</b>
	 * Number of keys that have been requested and found present.
	 *
	 * - <b>misses</b>
	 * Number of items that have been requested and not found.
	 *
	 * - <b>uptime</b>
	 * Time that the server is running.
	 *
	 * - <b>memory_usage</b>
	 * Memory used by this server to store items.
	 *
	 * - <b>memory_available</b>
	 * Memory allowed to use for storage.
	 *
	 * @since 2.2
	 *
	 * @return array|null An associative array with server's statistics if available, NULL otherwise.
	 */
	protected function doGetStats()
	{
		return null;
	}

	/**
	 * Flushes all cache entries.
	 *
	 * @return boolean TRUE if the cache entry was successfully deleted, FALSE otherwise.
	 */
	protected function doFlush()
	{
		$this->laravelCache->getStore()->flush();

		return true;
	}
}
 