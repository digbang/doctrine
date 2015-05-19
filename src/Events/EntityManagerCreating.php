<?php namespace Digbang\Doctrine\Events;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;

class EntityManagerCreating extends EventArgs
{
	/**
	 * @type array|\Doctrine\DBAL\Connection
	 */
	private $conn;
	/**
	 * @type Configuration
	 */
	private $config;
	/**
	 * @type EventManager
	 */
	private $eventManager;

	/**
	 * @param array|\Doctrine\DBAL\Connection $conn
	 * @param Configuration                   $config
	 * @param EventManager                    $eventManager
	 */
	public function __construct($conn, Configuration $config, EventManager $eventManager)
	{
		$this->conn         = $conn;
		$this->config       = $config;
		$this->eventManager = $eventManager;
	}

	/**
	 * @return array|\Doctrine\DBAL\Connection
	 */
	public function getConnection()
	{
		return $this->conn;
	}

	/**
	 * @return Configuration
	 */
	public function getConfiguration()
	{
		return $this->config;
	}

	/**
	 * @return EventManager
	 */
	public function getEventManager()
	{
		return $this->eventManager;
	}
}
