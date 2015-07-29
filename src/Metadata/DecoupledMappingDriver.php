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
	 * EntityMapping objects
	 * @type array
	 */
	private $entities = [];

	/**
	 * EmbeddableMapping objects
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
	 * @param EntityMapping|EmbeddableMapping $mapping
	 * @throws MappingException
	 * @return void
	 */
	public function addMapping($mapping)
	{
		if ($mapping instanceof EntityMapping)
		{
			return $this->addEntityMapping($mapping);
		}
		elseif ($mapping instanceof EmbeddableMapping)
		{
			return $this->addEmbeddableMapping($mapping);
		}

		throw new MappingException(
			'This driver expects an instance of ' .
			EntityMapping::class . ' or ' . EmbeddableMapping::class .
			', ' . get_class($mapping) . ' given.'
		);
	}

	/**
	 * Add an entity to the driver.
	 *
	 * @param EntityMapping $entityMapping
	 */
	private function addEntityMapping(EntityMapping $entityMapping)
	{
		$this->entities[$entityMapping->getEntityName()] = $entityMapping;
	}

	/**
	 * Add an embeddable to the driver.
	 *
	 * @param EmbeddableMapping $entityMapping
	 */
	private function addEmbeddableMapping(EmbeddableMapping $entityMapping)
	{
		$this->embeddables[$entityMapping->getEmbeddableName()] = $entityMapping;
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
		$mapping = $this->getMappingFor($className);

		$mapping->build($this->createBuilder($metadata, $mapping));
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
	 * @return EntityMapping|EmbeddableMapping
	 * @throws MappingException
	 */
	private function getMappingFor($className)
	{
		$mapping = array_get(array_merge($this->embeddables, $this->entities), $className);

		if (! $mapping)
		{
			throw new MappingException("Class '$className' does not have a mapping configuration.");
		}

		return $mapping;
	}

	/**
	 * @param ClassMetadata|ClassMetadataInfo $metadata
	 * @param EntityMapping|EmbeddableMapping $mapping
	 *
	 * @return Builder
	 */
	private function createBuilder(ClassMetadata $metadata, $mapping)
	{
		return new Builder(
			new ClassMetadataBuilder($metadata),
			$this->namingStrategy,
			$mapping instanceof EmbeddableMapping
		);
	}
}
