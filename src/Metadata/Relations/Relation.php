<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\AssociationBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;

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
 */
abstract class Relation
{
    /**
     * @var \Doctrine\ORM\Mapping\Builder\AssociationBuilder
     */
    protected $associationBuilder;

    /**
     * @var ClassMetadataInfo
     */
    protected $classMetadata;

    /**
     * @var string
     */
    protected $relation;

    /**
     * Relation constructor.
     *
     * @param AssociationBuilder $associationBuilder
     * @param ClassMetadataInfo  $classMetadata
     * @param string             $relation
     */
    public function __construct(AssociationBuilder $associationBuilder, ClassMetadataInfo $classMetadata, $relation)
    {
        $this->associationBuilder = $associationBuilder;
        $this->classMetadata      = $classMetadata;
        $this->relation           = $relation;
    }

    /**
     * @return \Doctrine\ORM\Mapping\Builder\AssociationBuilder
     *
     * @deprecated This object now works as a proxy through the magic __call method.
     */
    public function getAssociationBuilder()
    {
        return $this->associationBuilder;
    }

    public function build()
    {
        $this->associationBuilder->build();

        if (isset($this->classMetadata->cache))
        {
            $this->classMetadata->enableAssociationCache($this->relation, $this->classMetadata->cache);
        }
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
