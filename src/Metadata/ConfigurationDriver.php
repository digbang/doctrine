<?php namespace Digbang\Doctrine\Metadata;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

class ConfigurationDriver implements MappingDriver
{
	/**
	 * @type Repository
	 */
	private $config;

	/**
	 * @type \Illuminate\Container\Container
	 */
	private $container;

	/**
	 * @type array|null
	 */
	private $entities;

	/**
	 * @type array|null
	 */
	private $embeddables;

	function __construct(Repository $config, Container $container)
	{
		$this->config = $config;
		$this->container = $container;
	}

	/**
	 * Loads the metadata for the specified class into the provided container.
	 *
	 * @param string        $className
	 * @param ClassMetadata $metadata
	 *
	 * @throws \Doctrine\Common\Persistence\Mapping\MappingException
	 */
	public function loadMetadataForClass($className, ClassMetadata $metadata)
	{
		$this->loadFromConfig();

		$mappingClass = $this->getMappingFor($className);

		$metadataClass = $this->container->make($mappingClass);

		if (! $metadataClass instanceof EntityMapping)
		{
			throw MappingException::invalidMappingFile($className, get_class($metadataClass));
		}

		$builder = $this->container->make(Builder::class, [new ClassMetadataBuilder($metadata)]);

		$metadataClass->build($builder);
	}

	/**
	 * Gets the names of all mapped classes known to this driver.
	 *
	 * @return array The names of all mapped classes known to this driver.
	 */
	public function getAllClassNames()
	{
		$this->loadFromConfig();

		return array_merge(
			array_keys($this->entities),
			array_keys($this->embeddables)
		);
	}

	/**
	 * Returns whether the class with the specified name should have its metadata loaded.
	 * This is only the case if it is either mapped as an Entity or a MappedSuperclass.
	 *
	 * @param string $className
	 *
	 * @return boolean
	 */
	public function isTransient($className)
	{
		$this->loadFromConfig();

		return array_key_exists($className, $this->entities);
	}

	private function loadFromConfig()
	{
		if (null === $this->entities)
		{
			$this->entities = $this->config->get('doctrine::mappings.entities', []);
		}

		if (null === $this->embeddables)
		{
			$this->embeddables = $this->config->get('doctrine::mappings.embeddables', []);
		}
	}

	private function getMappingFor($className)
	{
		switch (true)
		{
			case array_key_exists($className, $this->entities):
				return $this->entities[$className];
			case array_key_exists($className, $this->embeddables):
				return $this->embeddables[$className];
			default:
				throw MappingException::nonExistingClass($className);
		}
	}
}
