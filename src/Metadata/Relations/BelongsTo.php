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
	 * @param string $inverseRelation
	 *
	 * @return $this
	 */
	public function inversedBy($inverseRelation)
	{
		$this->associationBuilder->inversedBy($inverseRelation);

		return $this;
	}

	/** TODO: When 2.5 integrates this change, we'll be able to use it!
	public function isPrimaryKey()
	{
		$this->associationBuilder->isPrimaryKey();

		return $this;
	}*/

	/**
	 * @return void
	 */
	public function build()
	{
		$this->associationBuilder->build();
	}
}
