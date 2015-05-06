<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\NamingStrategy;

class BelongsTo extends Relation
{
	private $keys = [];

	/**
	 * @type NamingStrategy
	 */
	private $namingStrategy;

	/**
	 * @type string
	 */
	private $relation;

	public function __construct(ClassMetadataBuilder $metadataBuilder, NamingStrategy $namingStrategy, $entityName, $relation)
	{
		$this->associationBuilder = $metadataBuilder->createManyToOne($relation, $entityName);
		$this->namingStrategy = $namingStrategy;
		$this->relation = $relation;
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

		$this->associationBuilder->build();
	}
}
