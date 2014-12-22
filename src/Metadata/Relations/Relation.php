<?php namespace Digbang\Doctrine\Metadata\Relations;

abstract class Relation
{
    /**
     * @type \Doctrine\ORM\Mapping\Builder\AssociationBuilder
     */
    protected $associationBuilder;

    /**
     * @return \Doctrine\ORM\Mapping\Builder\AssociationBuilder
     */
    public function getAssociationBuilder()
    {
        return $this->associationBuilder;
    }
}
