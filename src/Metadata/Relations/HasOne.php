<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class HasOne extends Relation implements RelationInterface
{
	public function __construct(ClassMetadataBuilder $metadataBuilder, $entityName, $relation)
	{
		$this->associationBuilder = $metadataBuilder->createOneToOne($relation, $entityName);
	}

	/**
	 * @param string $mappingRelation
	 *
	 * @return $this
	 */
	public function mappedBy($mappingRelation)
	{
		$this->associationBuilder->mappedBy($mappingRelation);

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
