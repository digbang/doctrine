<?php namespace Digbang\Doctrine\Metadata;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\NamingStrategy;

class DecoupledMappingDriver implements MappingDriver
{
	/**
	 * EntityMapping objects for entities
	 * @type array
	 */
	private $entities = [];

	/**
	 * EntityMapping objects for embeddables
	 * @type array
	 */
	private $embeddables = [];

	/**
	 * @type NamingStrategy
	 */
	private $namingStrategy;

	/**
	 * @param NamingStrategy $namingStrategy
	 */
	public function __construct(NamingStrategy $namingStrategy)
	{
		$this->namingStrategy = $namingStrategy;
	}

	/**
	 * Add an entity to the driver.
	 *
	 * @param EntityMapping $entityMapping
	 */
	public function addEntityMapping(EntityMapping $entityMapping)
	{
		$this->entities[$entityMapping->getEntityName()] = $entityMapping;
	}

	/**
	 * Add an embeddable to the driver.
	 *
	 * @param EntityMapping $entityMapping
	 */
	public function addEmbeddableMapping(EntityMapping $entityMapping)
	{
		$this->embeddables[$entityMapping->getEntityName()] = $entityMapping;
	}

	/**
	 * Loads the metadata for the specified class into the provided container.
	 *
	 * @param string        $className
	 * @param ClassMetadata $metadata
	 *
	 * @throws MappingException
	 */
	public function loadMetadataForClass($className, ClassMetadata $metadata)
	{
		$metadataClass = $this->getMappingFor($className);

		$metadataClass->build($this->createBuilder($metadata));
	}

	/**
	 * Gets the names of all mapped classes known to this driver.
	 *
	 * @return array The names of all mapped classes known to this driver.
	 */
	public function getAllClassNames()
	{
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
		return ! array_key_exists($className, $this->entities);
	}

	/**
	 * Get the mapping class that corresponds to the given entity or embeddable.
	 *
	 * @param string $className
	 * @return EntityMapping
	 * @throws MappingException
	 */
	private function getMappingFor($className)
	{
		$mappingClass = array_get(array_merge($this->embeddables, $this->entities), $className);

		if (! $mappingClass)
		{
			throw new MappingException("Class '$className' does not have a mapping configuration.");
		}

		return $mappingClass;
	}

	/**
	 * @param ClassMetadataInfo $metadata
	 * @return Builder
	 */
	private function createBuilder(ClassMetadata $metadata)
	{
		return new Builder(
			new ClassMetadataBuilder($metadata),
			$this->namingStrategy
		);
	}
}
