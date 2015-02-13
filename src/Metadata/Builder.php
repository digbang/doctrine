<?php namespace Digbang\Doctrine\Metadata;

use Closure;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;

/**
 * Builder around Doctrine's ClassMetadataBuilder.
 *
 * @package Digbang\Doctrine\Metadata
 *
 * @method $this getClassMetadata();
 * @method $this setMappedSuperClass()
 * @method $this setCustomRepositoryClass($repositoryClassName)
 * @method $this setReadOnly()
 * @method $this setTable($name)
 * @method $this addIndex(array $columns, $name)
 * @method $this addUniqueConstraint(array $columns, $name)
 * @method $this addNamedQuery($name, $dqlQuery)
 * @method $this setJoinedTableInheritance()
 * @method $this setSingleTableInheritance()
 * @method $this setDiscriminatorColumn($name, $type = 'string', $length = 255)
 * @method $this addDiscriminatorMapClass($name, $class)
 * @method $this setChangeTrackingPolicyDeferredExplicit()
 * @method $this setChangeTrackingPolicyNotify()
 * @method $this addLifecycleEvent($methodName, $event)
 * @method $this addField($name, $type, array $mapping = [])
 * @method $this createField($name, $type)
 * @method $this addManyToOne($name, $targetEntity, $inversedBy = null)
 * @method $this createManyToOne($name, $targetEntity)
 * @method $this createOneToOne($name, $targetEntity)
 * @method $this addInverseOneToOne($name, $targetEntity, $mappedBy)
 * @method $this addOwningOneToOne($name, $targetEntity, $inversedBy = null)
 * @method $this createManyToMany($name, $targetEntity)
 * @method $this addOwningManyToMany($name, $targetEntity, $inversedBy = null)
 * @method $this addInverseManyToMany($name, $targetEntity, $mappedBy)
 * @method $this createOneToMany($name, $targetEntity)
 * @method $this addOneToMany($name, $targetEntity, $mappedBy)
 */
class Builder
{
	/**
	 * @type \Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder
	 */
	private $metadataBuilder;

	public function __construct(ClassMetadataBuilder $metadataBuilder)
	{
		$this->metadataBuilder = $metadataBuilder;
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function table($name)
	{
		$this->metadataBuilder->setTable($name);

		return $this;
	}

	/**
	 * @param string $name
	 * @param string $type
	 *
	 * @return $this
	 */
	public function primary($name = 'id', $type = Type::BIGINT)
	{
		return $this->field($type, $name, function (FieldBuilder $fieldBuilder) use ($type) {
            $fieldBuilder->isPrimaryKey();

            if ($this->isInteger($type))
            {
                $fieldBuilder->generatedValue();
            }
        });
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function bigint($name, Closure $callback = null)
	{
		return $this->field(Type::BIGINT, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function boolean($name, Closure $callback = null)
	{
		return $this->field(Type::BOOLEAN, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function datetime($name, Closure $callback = null)
	{
		return $this->field(Type::DATETIME, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function datetimetz($name, Closure $callback = null)
	{
		return $this->field(Type::DATETIMETZ, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function date($name, Closure $callback = null)
	{
		return $this->field(Type::DATE, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function time($name, Closure $callback = null)
	{
		return $this->field(Type::TIME, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function decimal($name, Closure $callback = null)
	{
		return $this->field(Type::DECIMAL, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function integer($name, Closure $callback = null)
	{
		return $this->field(Type::INTEGER, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function object($name, Closure $callback = null)
	{
		return $this->field(Type::OBJECT, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function smallint($name, Closure $callback = null)
	{
		return $this->field(Type::SMALLINT, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function string($name, Closure $callback = null)
	{
		return $this->field(Type::STRING, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function text($name, Closure $callback = null)
	{
		return $this->field(Type::TEXT, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function binary($name, Closure $callback = null)
	{
		return $this->field(Type::BINARY, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function blob($name, Closure $callback = null)
	{
		return $this->field(Type::BLOB, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function float($name, Closure $callback = null)
	{
		return $this->field(Type::FLOAT, $name, $callback);
	}

	/**
	 * @param string   $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function guid($name, Closure $callback = null)
	{
		return $this->field(Type::GUID, $name, $callback);
	}

	/**
	 * @return $this
	 */
	public function timestamps()
	{
		$this->createdAt();
		$this->updatedAt();

		return $this;
	}

	/**
	 * @return $this
	 */
	public function createdAt()
	{
		$this->metadataBuilder->addLifecycleEvent('onPrePersist', Events::prePersist);

		return $this->datetime('createdAt');
	}

	/**
	 * @return $this
	 */
	public function updatedAt()
	{
		$this->metadataBuilder->addLifecycleEvent('onPreUpdate', Events::preUpdate);

		return $this->datetime('updatedAt');
	}

	/**
	 * @return $this
	 */
	public function deletedAt()
	{
		return $this->datetime('deletedAt', function(FieldBuilder $fieldBuilder){
            $fieldBuilder->nullable();
        });
	}

    /**
     * Alias of deletedAt
     *
     * @return $this
     */
    public function softDeletes()
    {
        return $this->deletedAt();
    }

	/**
	 * @param string        $type
	 * @param string        $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function field($type, $name, Closure $callback = null)
	{
		$fieldBuilder = $this->metadataBuilder->createField($name, $type);

		if ($callback)
		{
			$callback($fieldBuilder);
		}

		$fieldBuilder->build();

		return $this;
	}

	/**
	 * Sets the entity as embeddable.
	 *
	 * @return $this
	 */
	public function embeddable()
	{
		$classMetadata = $this->metadataBuilder->getClassMetadata();

		$classMetadata->isEmbeddedClass = true;
		$classMetadata->isMappedSuperclass = false;

		return $this;
	}

	/**
	 * Adds an embedded class to the entity.
	 *
	 * @param string $name
	 * @param string $class
	 * @param string|null $columnPrefix
	 *
	 * @return $this
	 */
	public function embedded($name, $class, $columnPrefix = null)
	{
		$classMetadata = $this->metadataBuilder->getClassMetadata();

		$classMetadata->mapEmbedded([
			'fieldName'    => $name,
			'class'        => $class,
			'columnPrefix' => $columnPrefix
		]);

		return $this;
	}

	/**
	 * Sets single table inheritance on the entity.
	 *
	 * @param string $typeColumn
	 * @return $this
	 *
	 * @see http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html#single-table-inheritance
	 */
	public function inheritance($typeColumn)
	{
		$this->metadataBuilder
			->setSingleTableInheritance()
			->setDiscriminatorColumn($typeColumn);

		return $this;
	}

	/**
	 * Adds a manyToOne relation to the entity.
	 *
	 * A callback can be passed as a third parameter. If so, the callback will
	 * receive an instance of Digbang\Doctrine\Metadata\Relations\BelongsTo
	 * to manipulate the relation.
	 *
	 * @param string $entityName
	 * @param string $relation
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function belongsTo($entityName, $relation, Closure $callback = null)
	{
		return $this->addRelation(
			new Relations\BelongsTo($this->metadataBuilder, $entityName, $relation),
			$callback
		);
	}

	/**
	 * Adds a manyToMany relation to the entity.
	 *
	 * A callback can be passed as a third parameter. If so, the callback will
	 * receive an instance of Digbang\Doctrine\Metadata\Relations\BelongsToMany
	 * to manipulate the relation.
	 *
	 * @param string $entityName
	 * @param string $relation
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function belongsToMany($entityName, $relation, Closure $callback = null)
	{
		return $this->addRelation(
			new Relations\BelongsToMany($this->metadataBuilder, $entityName, $relation),
			$callback
		);
	}

	/**
	 * Adds a oneToMany relation to the entity.
	 *
	 * A callback can be passed as a third parameter. If so, the callback will
	 * receive an instance of Digbang\Doctrine\Metadata\Relations\HasMany
	 * to manipulate the relation.
	 *
	 * @param string $entityName
	 * @param string $relation
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function hasMany($entityName, $relation, Closure $callback = null)
	{
		return $this->addRelation(
			new Relations\HasMany($this->metadataBuilder, $entityName, $relation),
			$callback
		);
	}

	/**
	 * Adds a oneToOne relation to the entity.
	 *
	 * A callback can be passed as a third parameter. If so, the callback will
	 * receive an instance of Digbang\Doctrine\Metadata\Relations\BelongsToMany
	 * to manipulate the relation.
	 *
	 * @param string $entityName
	 * @param string $relation
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function hasOne($entityName, $relation, Closure $callback = null)
	{
		return $this->addRelation(
			new Relations\HasOne($this->metadataBuilder, $entityName, $relation),
			$callback
		);
	}

	/**
	 * Adds a custom relation to the entity.
	 * The relation needs to extend the
	 * Digbang\Doctrine\Metadata\Relations\Relation abstract class.
	 *
	 * @param \Digbang\Doctrine\Metadata\Relations\Relation $relation
	 * @param callable|null                                 $callback
	 *
	 * @return $this
	 */
	public function addRelation(Relations\Relation $relation, Closure $callback = null)
	{
		if ($callback)
		{
			$callback($relation);
		}

		$relation->build();

		return $this;
	}

	/**
	 * Get Doctrine's metadata builder object, for full control of the relation build.
	 * @return ClassMetadataBuilder
	 *
	 * @deprecated This object now works as a proxy through the magic __call method.
	 */
	public function getMetadataBuilder()
	{
		return $this->metadataBuilder;
	}

	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return $this
	 * @throws \BadMethodCallException
	 */
	public function __call($name, $arguments)
	{
		if (method_exists($this->metadataBuilder, $name))
		{
			call_user_func_array([$this->metadataBuilder, $name], $arguments);

			return $this;
		}

		throw new \BadMethodCallException("Method '$name' does not exist.");
	}

	/**
	 * Check if a given type is any of the possible integer types.
	 *
	 * @param string $type
	 * @return bool
	 */
	protected function isInteger($type)
	{
		return in_array($type, [Type::INTEGER, Type::BIGINT, Type::SMALLINT]);
	}
}
