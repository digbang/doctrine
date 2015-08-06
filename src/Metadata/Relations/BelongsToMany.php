<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

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
}
