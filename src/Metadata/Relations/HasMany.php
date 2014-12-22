<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class HasMany extends Relation implements RelationInterface
{
	/**
	 * @type array
	 */
	private $orderColumns = [];

	public function __construct(ClassMetadataBuilder $metadataBuilder, $entityName, $relation)
	{
		$this->associationBuilder = $metadataBuilder->createOneToMany($relation, $entityName);
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
	 * @param string $columnName
	 * @param string $sortOrder
	 *
	 * @return $this
	 */
	public function orderBy($columnName, $sortOrder = 'asc')
	{
		$this->orderColumns[$columnName] = $sortOrder;

		$this->associationBuilder->setOrderBy($this->orderColumns);

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
