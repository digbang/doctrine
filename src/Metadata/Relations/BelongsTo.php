<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class BelongsTo extends Relation implements RelationInterface
{
	public function __construct(ClassMetadataBuilder $metadataBuilder, $entityName, $relation)
	{
		$this->associationBuilder = $metadataBuilder->createManyToOne($relation, $entityName);
	}

	/**
	 * @param string $foreignKey
	 * @param string $otherKey
	 *
	 * @return $this
	 */
	public function keys($foreignKey, $otherKey = 'id')
	{
		$this->associationBuilder->addJoinColumn($foreignKey, $otherKey);

		return $this;
	}

	/**
	 * @param string $inverseRelation
	 *
	 * @return $this
	 */
	public function inversedBy($inverseRelation)
	{
		$this->associationBuilder->inversedBy($inverseRelation);

		return $this;
	}

	public function isPrimaryKey()
	{
		$this->associationBuilder->isPrimaryKey();

		return $this;
	}

	/**
	 * @return void
	 */
	public function build()
	{
		$this->associationBuilder->build();
	}
}
