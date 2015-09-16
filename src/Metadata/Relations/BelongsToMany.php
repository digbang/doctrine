<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Class BelongsToMany
 *
 * @package Digbang\Doctrine\Metadata\Relations
 * @method $this mappedBy($fieldName)
 * @method $this inversedBy($fieldName)
 * @method $this orphanRemoval()
 * @method $this setJoinTable($name)
 * @method $this addInverseJoinColumn($columnName, $referencedColumnName, $nullable = true, $unique = false, $onDelete = null, $columnDef = null)
 * @method $this setIndexBy($fieldName)
 */
class BelongsToMany extends Relation
{
	/**
     * @type \Doctrine\ORM\Mapping\Builder\ManyToManyAssociationBuilder
     */
    protected $associationBuilder;

    /**
     * @type array
     */
    private $orderColumns = [];

	/**
	 * @param ClassMetadataBuilder $metadataBuilder
	 * @param string               $entityName
	 * @param string               $relation
	 */
    public function __construct(ClassMetadataBuilder $metadataBuilder, $entityName, $relation)
    {
        $this->associationBuilder = $metadataBuilder->createManyToMany(
            $relation, $entityName
        );
    }

	/**
	 * Change the name of the join table.
	 *
	 * @param string $tableName
	 * @return $this
	 */
	public function tableName($tableName)
	{
		$this->associationBuilder->setJoinTable($tableName);

		return $this;
	}

	/**
	 * Change the default foreign key in the join table.
	 *
	 * @param string $foreignKey
	 * @param string $references
	 *
	 * @return $this
	 */
    public function foreignKeys($foreignKey, $references = 'id')
    {
        $this->associationBuilder->addJoinColumn($foreignKey, $references, false);

        return $this;
    }

	/**
	 * Change the default inverse key in the join table.
	 *
	 * @param string $inverseKey
	 * @param string $references
	 *
	 * @return $this
	 */
    public function inverseKeys($inverseKey, $references = 'id')
    {
        $this->associationBuilder->addInverseJoinColumn($inverseKey, $references, false);

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
     * @return \Doctrine\ORM\Mapping\Builder\ManyToManyAssociationBuilder
     *
     * @deprecated This object now works as a proxy through the magic __call method.
     */
    public function getAssociationBuilder()
    {
	    return parent::getAssociationBuilder();
    }
}
