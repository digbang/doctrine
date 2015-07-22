<?php namespace Digbang\Doctrine\Bridges;

use Illuminate\Contracts\Cache\Factory;
use Doctrine\Common\Cache\CacheProvider;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Cache\Store;

class CacheBridge extends CacheProvider
{
	/**
	 * @type Repository|Store
	 */
	private $store;

	/**
	 * @type Factory
	 */
	private $cacheFactory;

	public function __construct(Factory $cacheFactory)
	{
		$this->cacheFactory = $cacheFactory;
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
		if (($data = $this->store()->get($id)) !== null)
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
		return $this->store()->has($id);
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
		if ($lifeTime === 0)
		{
			$this->store()->forever($id, $data);
		}
		else
		{
			$this->store()->put($id, $data, $lifeTime / 60);
		}

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
		$this->store()->forget($id);

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
		$this->store()->flush();

		return true;
	}

	/**
	 * @return Repository|Store
	 */
	private function store()
	{
		if (! $this->store)
		{
			$this->store = $this->cacheFactory->store();
		}

		return $this->store;
	}
}
 