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
            $metadataBuilder->createManyToMany($relation, $entityName),
            $metadataBuilder->getClassMetadata(),
            $relation
        );
    }

    /**
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
