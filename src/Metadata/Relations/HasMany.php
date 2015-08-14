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
	 * @type array
	 */
	private $orderColumns = [];

	public function __construct(ClassMetadataBuilder $metadataBuilder, $entityName, $relation)
	{
		$this->associationBuilder = $metadataBuilder->createOneToMany($relation, $entityName);
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
	 * @return \Doctrine\ORM\Mapping\Builder\OneToManyAssociationBuilder
	 *
	 * @deprecated This object now works as a proxy through the magic __call method.
	 */
	public function getAssociationBuilder()
	{
		return parent::getAssociationBuilder();
	}
}
