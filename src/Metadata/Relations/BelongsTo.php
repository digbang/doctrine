<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\NamingStrategy;

/**
 * Class BelongsTo
 *
 * @package Digbang\Doctrine\Metadata\Relations
 * @method $this orphanRemoval()
 * @method $this inversedBy($fieldName)
 */
class BelongsTo extends Relation
{
	/**
	 * @var array[]
	 */
	private $keys = [];

	/**
	 * @type NamingStrategy
	 */
	private $namingStrategy;

	/**
	 * @param ClassMetadataBuilder $metadataBuilder
	 * @param NamingStrategy       $namingStrategy
	 * @param string               $entityName
	 * @param string               $relation
	 */
	public function __construct(ClassMetadataBuilder $metadataBuilder, NamingStrategy $namingStrategy, $entityName, $relation)
	{
		parent::__construct(
			$metadataBuilder->createManyToOne($relation, $entityName),
			$metadataBuilder->getClassMetadata(),
			$relation
		);

		$this->namingStrategy = $namingStrategy;
	}

	/**
	 * @param string $foreignKey
	 * @param string $otherKey
	 * @param bool   $nullable
	 *
	 * @return $this
	 */
	public function keys($foreignKey, $otherKey = 'id', $nullable = false)
	{
		$this->keys[] = [$foreignKey, $otherKey, $nullable];

		return $this;
	}

	/**
	 * @return $this
	 */
	public function isPrimaryKey()
	{
		$this->associationBuilder->makePrimaryKey();

		return $this;
	}

	public function build()
	{
		if (empty($this->keys))
		{
			$this->keys(
				$this->namingStrategy->joinColumnName($this->relation),
                $this->namingStrategy->referenceColumnName(),
				false
			);
		}

		foreach ($this->keys as $key)
		{
			list($foreignKey, $otherKey, $nullable) = $key;

			$this->associationBuilder->addJoinColumn($foreignKey, $otherKey, $nullable);
		}

		parent::build();
	}
}
