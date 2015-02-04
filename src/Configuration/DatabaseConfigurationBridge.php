<?php namespace Digbang\Doctrine\Configuration;

use Doctrine\DBAL\Connection;
use Illuminate\Contracts\Config\Repository;

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
	 * @type \Illuminate\Contracts\Config\Repository
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
			'host'     => $configuration['host'],
			'user'     => $configuration['username'],
			'password' => $configuration['password'],
			'charset'  => $configuration['charset']
		];

		if ($driver == 'sqlite')
		{
			$config['path'] = $configuration['database'];
		}
		else
		{
			$config['dbname'] = $configuration['database'];
		}

		return $config;
	}
}
 