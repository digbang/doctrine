<?php namespace Digbang\Doctrine\Metadata;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\Instantiator\InstantiatorInterface;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\NamingStrategy;
use Illuminate\Contracts\Config\Repository;

class DecoupledMappingDriver implements MappingDriver
{
	/**
	 * @type array
	 */
	private $entities = [];

	/**
	 * @type array
	 */
	private $embeddables = [];

	/**
	 * @type bool
	 */
	private $loaded = false;

	/**
	 * @type Repository
	 */
	private $config;

	/**
	 * @type NamingStrategy
	 */
	private $namingStrategy;

	/**
	 * @type InstantiatorInterface
	 */
	private $instantiator;

	/**
	 * @param Repository            $config
	 * @param NamingStrategy        $namingStrategy
	 * @param InstantiatorInterface $instantiator
	 */
	public function __construct(Repository $config, NamingStrategy $namingStrategy, InstantiatorInterface $instantiator)
	{
		$this->config         = $config;
		$this->namingStrategy = $namingStrategy;
		$this->instantiator   = $instantiator;
	}

	/**
	 * Loads the metadata for the specified class into the provided container.
	 *
	 * @param string            $className
	 * @param ClassMetadataInfo $metadata
	 *
	 * @throws \Doctrine\Common\Persistence\Mapping\MappingException
	 */
	public function loadMetadataForClass($className, ClassMetadata $metadata)
	{
		$this->loadFromConfig();

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

		return ! array_key_exists($className, $this->entities);
	}

	/**
	 * Loads all entityMappings from the package configuration file
	 */
	private function loadFromConfig()
	{
		if (! $this->loaded)
		{
			foreach ($this->config->get('doctrine-mappings.entities', []) as $entityMapping)
			{
				/** @type $entityMapping EntityMapping */
				$this->entities[$entityMapping::getEntityName()] = $entityMapping;
			}

			foreach ($this->config->get('doctrine-mappings.embeddables', []) as $entityMapping)
			{
				/** @type $entityMapping EntityMapping */
				$this->embeddables[$entityMapping::getEntityName()] = $entityMapping;
			}

			$this->loaded = true;
		}
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

		$metadataClass = $this->instantiator->instantiate($mappingClass);

		if (! $metadataClass instanceof EntityMapping)
		{
			throw MappingException::invalidMappingFile($className, get_class($metadataClass));
		}

		return $metadataClass;
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
