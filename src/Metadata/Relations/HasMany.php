<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Class HasMany
 *
 * @package Digbang\Doctrine\Metadata\Relations
 * @method $this orphanRemoval()
 * @method $this mappedBy($fieldName)
 * @method $this setIndexBy($fieldName)
 */
class HasMany extends Relation
{
	/**
     * @type \Doctrine\ORM\Mapping\Builder\OneToManyAssociationBuilder
     */
    protected $associationBuilder;

	/**
	 * @type string[]
	 */
	private $orderColumns = [];

	/**
	 * @param ClassMetadataBuilder $metadataBuilder
	 * @param string               $entityName
	 * @param string               $relation
	 */
	public function __construct(ClassMetadataBuilder $metadataBuilder, $entityName, $relation)
	{
		parent::__construct(
			$metadataBuilder->createOneToMany($relation, $entityName),
			$metadataBuilder->getClassMetadata(),
			$relation
		);
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
}
