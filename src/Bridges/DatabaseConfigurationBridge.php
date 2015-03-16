<?php namespace Digbang\Doctrine\Bridges;

use Doctrine\DBAL\Connection;
use Illuminate\Config\Repository;

class DatabaseConfigurationBridge
{
	/**
	 * @type array
	 */
	private $doctrineDrivers = [
		'mysql' => 'pdo_mysql',
		'pgsql' => 'pdo_pgsql',
		'sqlsrv' => 'pdo_sqlsrv',
		'sqlite' => 'pdo_sqlite'
	];

	/**
	 * @type \Illuminate\Config\Repository
	 */
	private $config;

	/**
	 * @type Connection
	 */
	private $connection;

	public function __construct(Repository $config)
	{
		$this->config = $config;
	}

	public function getConnection()
	{
		if (! $this->connection)
		{
			$this->connection = $this->newConnection();
		}

		return $this->connection;
	}

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
 