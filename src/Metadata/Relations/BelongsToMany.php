<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

class BelongsToMany extends Relation implements RelationInterface
{
    /**
     * @type array
     */
    private $orderColumns = [];

    function __construct(ClassMetadataBuilder $metadataBuilder, $entityName, $relation)
    {
        $this->associationBuilder = $metadataBuilder->createManyToMany(
            $relation, $entityName
        );
    }

    public function mappedBy($fieldName)
    {
        $this->associationBuilder->mappedBy($fieldName);

        return $this;
    }

    public function inversedBy($fieldName)
    {
        $this->associationBuilder->inversedBy($fieldName);

        return $this;
    }

    public function foreignKeys($foreignKey, $references = 'id')
    {
        $this->associationBuilder->addJoinColumn($foreignKey, $references, false);

        return $this;
    }

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
     * @return void
     */
    public function build()
    {
        $this->associationBuilder->build();
    }
}
