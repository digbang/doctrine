<?php namespace Digbang\Doctrine\Metadata\Relations;

/**
 * @method $this cascadeAll()
 * @method $this cascadePersist()
 * @method $this cascadeRemove()
 * @method $this cascadeMerge()
 * @method $this cascadeDetach()
 * @method $this cascadeRefresh()
 * @method $this fetchExtraLazy()
 * @method $this fetchEager()
 * @method $this fetchLazy()
 * @method $this addJoinColumn($columnName, $referencedColumnName, $nullable = true, $unique = false, $onDelete = null, $columnDef = null)
 * @method $this build()
 */
abstract class Relation
{
    /**
     * @type \Doctrine\ORM\Mapping\Builder\AssociationBuilder
     */
    protected $associationBuilder;

    /**
     * @return \Doctrine\ORM\Mapping\Builder\AssociationBuilder
     *
     * @deprecated This object now works as a proxy through the magic __call method.
     */
    public function getAssociationBuilder()
    {
        return $this->associationBuilder;
    }

    /**
     * Magic call method works as a proxy for the Doctrine associationBuilder
     *
     * @param string $method
     * @param array $args
     *
     * @return $this
     * @throws \BadMethodCallException
     */
    public function __call($method, $args)
    {
        if (method_exists($this->associationBuilder, $method))
        {
            call_user_func_array([$this->associationBuilder, $method], $args);

            return $this;
        }

        throw new \BadMethodCallException("Method '$method' does not exist.");
    }
}
