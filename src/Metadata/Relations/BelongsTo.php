<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class BelongsTo extends Relation
{
	public function __construct(ClassMetadataBuilder $metadataBuilder, $entityName, $relation)
	{
		$this->associationBuilder = $metadataBuilder->createManyToOne($relation, $entityName);
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
		$this->associationBuilder->addJoinColumn($foreignKey, $otherKey, $nullable);

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
}
