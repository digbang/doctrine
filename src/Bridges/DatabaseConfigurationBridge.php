<?php namespace Digbang\Doctrine\Bridges;

use Doctrine\DBAL\Connection;
use Illuminate\Contracts\Config\Repository;

class DatabaseConfigurationBridge
{
	/**
	 * @type array
	 */
	private $doctrineDrivers = [
		'mysql'  => 'pdo_mysql',
		'pgsql'  => 'pdo_pgsql',
		'sqlsrv' => 'pdo_sqlsrv',
		'sqlite' => 'pdo_sqlite'
	];

	/**
	 * @type Repository
	 */
	private $config;

	/**
	 * @type Connection
	 */
	private $connection;

	/**
	 * @type CacheBridge
	 */
	private $cache;

	/**
	 * @param Repository  $config
	 * @param CacheBridge $cache
	 */
	public function __construct(Repository $config, CacheBridge $cache)
	{
		$this->config = $config;
		$this->cache = $cache;
	}

	/**
	 * Get the doctrine connection array
	 *
	 * @return array
	 */
	public function getConnection()
	{
		if (! $this->connection)
		{
			$this->connection = $this->newConnection();
		}

		return $this->connection;
	}

	/**
	 * Build the doctrine connection array based on the laravel configuration.
	 *
	 * @return array
	 */
	public function newConnection()
	{
		$defaultConnection = $this->config->get('database.default');

		$configuration = $this->config->get("database.connections.$defaultConnection");

		$driver = $configuration['driver'];
		if (! array_key_exists($driver, $this->doctrineDrivers))
		{
			throw new \UnexpectedValueException("Driver $driver not available in Doctrine. Choose one of " . implode(', ', array_keys($this->doctrineDrivers)));
		}

		$config = [
			'driver'   => $this->doctrineDrivers[$driver],
			'host'     => array_get($configuration, 'host'),
			'user'     => array_get($configuration, 'username'),
			'password' => array_get($configuration, 'password'),
			'charset'  => array_get($configuration, 'charset')
		];

		if (($serverVersion = array_get($configuration, 'server_version')) !== null)
		{
			$config['serverVersion'] = $serverVersion;
		}
		elseif ($this->cache->contains('database.version'))
		{
			$config['serverVersion'] = $this->cache->fetch('database.version');
		}

		if ($driver == 'sqlite')
		{
			$config['path'] = array_get($configuration, 'database');
		}
		else
		{
			$config['dbname'] = array_get($configuration, 'database');
		}

		return $config;
	}
}
