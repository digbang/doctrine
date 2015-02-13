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
 * @method $this getClassMetadata()
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
     * @param bool $nullable
     * @param int  $algorithm one of PASSWORD_BCRYPT or PASSWORD_DEFAULT
     *
     * @return $this
     */
    public function password($nullable = true, $algorithm = PASSWORD_BCRYPT)
    {
        return $this->string('password', function(FieldBuilder $fieldBuilder) use ($nullable, $algorithm){
            $fieldBuilder->nullable($nullable);
            $fieldBuilder->length($algorithm == PASSWORD_BCRYPT ? 60 : 255);
        });
    }

    /**
     * @return $this
     */
    public function rememberToken()
    {
        return $this->string('rememberToken', function(FieldBuilder $fieldBuilder){
            $fieldBuilder->columnName('remember_token');
            $fieldBuilder->nullable();
        });
    }

    /**
     * Helper function to create a basic authenticated user mapping.
     * Includes id, rememberToken, password, timestamps and softDeletes.
     *
     * To take full advantage of this, use the corresponding trait that matches
     * your Identity selection:
     * - <strong>IntIdentityTrait</strong> for "id" identity (auto-generated)
     * - <strong>EmailIdentityTrait</strong> for "email" identity
     * - <strong>UsernameIdentityTrait</strong> for "username" identity
     *
     * @param string $idField
     * @param string $idType
     *
     * @return $this
     */
    public function auth($idField = 'id', $idType = Type::BIGINT)
    {
        $this->primary($idField, $idType);

        if ($idField != 'email')
        {
            $this->string('email', function(FieldBuilder $fieldBuilder){
                $fieldBuilder->unique();
            });
        }

        $this->password();
        $this->rememberToken();
        $this->timestamps();
        $this->softDeletes();

        return $this;
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
     * Sets single table inheritance on the entity.
     *
     * @param string $typeColumn
     * @param int $inheritanceType One of the constants defined in the \Digbang\Doctrine\Metadata\Inheritance interface.
     *
     * @return $this
     * @link http://doctrine-orm.readthedocs.org/en/latest/reference/inheritance-mapping.html
     */
	public function inheritance($typeColumn, $inheritanceType = Inheritance::SINGLE)
	{
        switch ($inheritanceType)
        {
            case Inheritance::SINGLE:
                $this->metadataBuilder->setSingleTableInheritance();
                break;
            case Inheritance::JOINED:
                $this->metadataBuilder->setJoinedTableInheritance();
                break;
            case Inheritance::NONE:
                $this->metadataBuilder->getClassMetadata()->setInheritanceType(Inheritance::NONE);
                break;
            default:
                throw new \UnexpectedValueException(
                    "Unexpected inheritance type: $inheritanceType. One of 'single' or 'joined' is required."
                );
        }

        $this->metadataBuilder->setDiscriminatorColumn($typeColumn);

		return $this;
	}

    /**
     * Adds an embedded class to the entity.
     * Third parameter lets you add a prefix to customize how the embedded columns
     * are built in this particular entity.
     * Default is "no prefix" (false).
     *
     * <strong>WARNING:</strong> A <em>null</em> value will use the entity's name as prefix,
     * as this is the default Doctrine behavior. Try not to use it, and put the literal string
     * you intend to use instead.
     *
     * @param string           $class
     * @param string           $name
     * @param string|bool|null $columnPrefix
     *
     * @return $this
     */
    public function embedded($class, $name, $columnPrefix = false)
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
	 * Adds a manyToOne relation to the entity.
	 *
	 * A callback can be passed as a third parameter. If so, the callback will
	 * receive an instance of Digbang\Doctrine\Metadata\Relations\BelongsTo
	 * to manipulate the relation.
	 *
	 * @param string        $entity
	 * @param string        $field
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function belongsTo($entity, $field, Closure $callback = null)
	{
		return $this->addRelation(
			new Relations\BelongsTo($this->metadataBuilder, $entity, $field),
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
	 * @param string $entity
	 * @param string $field
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function belongsToMany($entity, $field, Closure $callback = null)
	{
		return $this->addRelation(
			new Relations\BelongsToMany($this->metadataBuilder, $entity, $field),
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
	 * @param string $entity
	 * @param string $field
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function hasMany($entity, $field, Closure $callback = null)
	{
		return $this->addRelation(
			new Relations\HasMany($this->metadataBuilder, $entity, $field),
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
	 * @param string $entity
	 * @param string $field
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function hasOne($entity, $field, Closure $callback = null)
	{
		return $this->addRelation(
			new Relations\HasOne($this->metadataBuilder, $entity, $field),
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
