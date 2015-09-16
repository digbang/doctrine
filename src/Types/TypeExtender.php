<?php namespace Digbang\Doctrine\Types;

use Doctrine\DBAL\Event\ConnectionEventArgs;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class TypeExtender
{
	/**
	 * @type TypeExtender
	 */
	private static $instance;

	/**
	 * @type array
	 */
	private $extensions = [];

	/**
	 * Disallow creation (forces static constructor)
	 */
	private final function __construct(){}

	/**
	 * Get the singleton instance
	 *
	 * @return TypeExtender
	 */
	public static function instance()
	{
		if (! self::$instance)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Add a custom type.
	 *
	 * @param string $type   The type identifier
	 * @param string $dbType The type, as will be used in the current platform
	 * @param string $class  The class representing the type
	 *
	 * @return $this
	 */
	public function add($type, $dbType, $class)
	{
		$this->extensions[] = [$type, $dbType, $class];

		return $this;
	}

	/**
	 * Applies the configured extensions to the Doctrine Type mapper.
	 *
	 * @throws \Doctrine\DBAL\DBALException
	 */
	public function apply()
	{
		foreach ($this->extensions as list($type, $dbType, $class))
		{
			if (Type::hasType($type))
			{
				Type::overrideType($type, $class);
			}
			else
			{
				Type::addType($type, $class);
			}
		}
	}

	/**
	 * Registers the configured extensions in the current platform driver.
	 *
	 * @param AbstractPlatform $platform
	 * @throws \Doctrine\DBAL\DBALException
	 */
	private function register(AbstractPlatform $platform)
	{
		foreach ($this->extensions as list($type, $dbType, $class))
		{
			$platform->registerDoctrineTypeMapping($dbType, $type);
		}
	}

	/**
	 * Apply registered types.
	 *
	 * @param ConnectionEventArgs $args
	 */
	public function postConnect(ConnectionEventArgs $args)
	{
		$this->register($args->getDatabasePlatform());
	}
}
