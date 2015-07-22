<?php namespace Digbang\Doctrine\Metadata;

use Digbang\Doctrine\Types\CarbonType;
use Digbang\Doctrine\Types\TsvectorType;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\NamingStrategy;

/**
 * Builder around Doctrine's ClassMetadataBuilder.
 *
 * @package Digbang\Doctrine\Metadata
 *
 * @method $this getClassMetadata()
 * @method $this setMappedSuperClass()
 * @method $this setCustomRepositoryClass($repositoryClassName)
 * @method $this setReadOnly()
 * @method $this addIndex(array $columns, $name)
 * @method $this addUniqueConstraint(array $columns, $name)
 * @method $this addNamedQuery($name, $dqlQuery)
 * @method $this setDiscriminatorColumn($name, $type = 'string', $length = 255)
 * @method $this addDiscriminatorMapClass($name, $class)
 * @method $this setChangeTrackingPolicyDeferredExplicit()
 * @method $this setChangeTrackingPolicyNotify()
 * @method $this addLifecycleEvent($methodName, $event)
 * @method $this nullableBigint($name, callable $callback = null)
 * @method $this nullableBoolean($name, callable $callback = null)
 * @method $this nullableDatetime($name, callable $callback = null)
 * @method $this nullableDatetimetz($name, callable $callback = null)
 * @method $this nullableDate($name, callable $callback = null)
 * @method $this nullableTime($name, callable $callback = null)
 * @method $this nullableDecimal($name, callable $callback = null)
 * @method $this nullableInteger($name, callable $callback = null)
 * @method $this nullableObject($name, callable $callback = null)
 * @method $this nullableSmallint($name, callable $callback = null)
 * @method $this nullableString($name, callable $callback = null)
 * @method $this nullableText($name, callable $callback = null)
 * @method $this nullableBinary($name, callable $callback = null)
 * @method $this nullableBlob($name, callable $callback = null)
 * @method $this nullableFloat($name, callable $callback = null)
 * @method $this nullableGuid($name, callable $callback = null)
 * @method $this nullableTsvector($name, callable $callback = null)
 * @method $this nullableField($type, $name, callable $callback = null)
 * @method $this uniqueBigint($name, callable $callback = null)
 * @method $this uniqueBoolean($name, callable $callback = null)
 * @method $this uniqueDatetime($name, callable $callback = null)
 * @method $this uniqueDatetimetz($name, callable $callback = null)
 * @method $this uniqueDate($name, callable $callback = null)
 * @method $this uniqueTime($name, callable $callback = null)
 * @method $this uniqueDecimal($name, callable $callback = null)
 * @method $this uniqueInteger($name, callable $callback = null)
 * @method $this uniqueObject($name, callable $callback = null)
 * @method $this uniqueSmallint($name, callable $callback = null)
 * @method $this uniqueString($name, callable $callback = null)
 * @method $this uniqueText($name, callable $callback = null)
 * @method $this uniqueBinary($name, callable $callback = null)
 * @method $this uniqueBlob($name, callable $callback = null)
 * @method $this uniqueFloat($name, callable $callback = null)
 * @method $this uniqueGuid($name, callable $callback = null)
 * @method $this uniqueTsvector($name, callable $callback = null)
 * @method $this uniqueField($type, $name, callable $callback = null)
 */
class Builder
{
	/**
	 * flyweight array of available field types
	 * @type array
	 */
	private $types = [
		'bigint',
		'boolean',
		'datetime',
		'datetimetz',
		'date',
		'time',
		'decimal',
		'integer',
		'object',
		'smallint',
		'string',
		'text',
		'binary',
		'blob',
		'float',
		'guid',
		'tsvector'
	];

	/**
	 * @type \Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder
	 */
	private $metadataBuilder;

	/**
	 * @type NamingStrategy
	 */
	private $namingStrategy;

	public function __construct(ClassMetadataBuilder $metadataBuilder, NamingStrategy $namingStrategy)
	{
		$this->metadataBuilder = $metadataBuilder;
		$this->namingStrategy  = $namingStrategy;
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
            $fieldBuilder->makePrimaryKey();

            if ($this->isInteger($type))
            {
                $fieldBuilder->generatedValue('IDENTITY');
            }
        });
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function bigint($name, callable $callback = null)
	{
		return $this->field(Type::BIGINT, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function boolean($name, callable $callback = null)
	{
		return $this->field(Type::BOOLEAN, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function datetime($name, callable $callback = null)
	{
		return $this->field(CarbonType::DATETIME, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function datetimetz($name, callable $callback = null)
	{
		return $this->field(CarbonType::DATETIMETZ, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function date($name, callable $callback = null)
	{
		return $this->field(CarbonType::DATE, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function time($name, callable $callback = null)
	{
		return $this->field(CarbonType::TIME, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function decimal($name, callable $callback = null)
	{
		return $this->field(Type::DECIMAL, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function integer($name, callable $callback = null)
	{
		return $this->field(Type::INTEGER, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function object($name, callable $callback = null)
	{
		return $this->field(Type::OBJECT, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function smallint($name, callable $callback = null)
	{
		return $this->field(Type::SMALLINT, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function string($name, callable $callback = null)
	{
		return $this->field(Type::STRING, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function text($name, callable $callback = null)
	{
		return $this->field(Type::TEXT, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function binary($name, callable $callback = null)
	{
		return $this->field(Type::BINARY, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function blob($name, callable $callback = null)
	{
		return $this->field(Type::BLOB, $name, $callback);
	}

	/**
	 * @param string $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function float($name, callable $callback = null)
	{
		return $this->field(Type::FLOAT, $name, $callback);
	}

	/**
	 * @param string   $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function guid($name, callable $callback = null)
	{
		return $this->field(Type::GUID, $name, $callback);
	}

	/**
	 * @param string   $name
	 * @param callable $callback
	 *
	 * @return $this
	 */
	public function tsvector($name, callable $callback = null)
	{
		return $this->field(TsvectorType::TSVECTOR, $name, $callback);
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
            $this->uniqueString('email');
        }

        $this->password();
        $this->rememberToken();
        $this->timestamps();
        $this->deletedAt();

        return $this;
    }

	/**
	 * @param string        $type
	 * @param string        $name
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function field($type, $name, callable $callback = null)
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
		$this->metadataBuilder->setEmbeddable();

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
        $this->metadataBuilder->addEmbedded($name, $class, $columnPrefix);

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
	public function belongsTo($entity, $field, callable $callback = null)
	{
		return $this->addRelation(
			new Relations\BelongsTo($this->metadataBuilder, $this->namingStrategy, $entity, $field),
			$callback
		);
	}

    /**
	 * Adds an optional manyToOne relation to the entity.
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
	public function mayBelongTo($entity, $field, callable $callback = null)
	{
		return $this->addRelation(
			new Relations\BelongsTo($this->metadataBuilder, $this->namingStrategy, $entity, $field),
			$this->makeMayBelongToCallback($field, $callback)
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
	public function belongsToMany($entity, $field, callable $callback = null)
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
	public function hasMany($entity, $field, callable $callback = null)
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
	public function hasOne($entity, $field, callable $callback = null)
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
	public function addRelation(Relations\Relation $relation, callable $callback = null)
	{
		if ($callback)
		{
			$callback($relation);
		}

		$relation->build();

		return $this;
	}

	/**
	 * Enables second-level cache on this entity.
	 *
	 * If you want to enable second-level cache,
	 * you must enable it on the EntityManager configuration.
	 *
	 * Depending on the cache mode selected, you may also need to configure
	 * lock modes.
	 *
	 * @param int         $usage  Cache mode. use ClassMetadataInfo::CACHE_USAGE_* constants.
	 *                            Defaults to READ_ONLY mode.
	 * @param string|null $region The cache region to be used. Doctrine will use a default region
	 *                            for each entity, if none is provided.
	 *
	 * @return $this
	 * @see http://doctrine-orm.readthedocs.org/en/latest/reference/second-level-cache.html
	 */
	public function cacheable($usage = ClassMetadataInfo::CACHE_USAGE_READ_ONLY, $region = null)
	{
		$classMetadata = $this->metadataBuilder->getClassMetadata();
		$classMetadata->enableCache(compact('usage', $region === null ?: 'region'));

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
		if (strpos($name, 'nullable') !== false)
		{
			$method = lcfirst(str_replace('nullable', '', $name));
			return $this->callNullable($method, $arguments);
		}

		if (strpos($name, 'unique') !== false)
		{
			$method = lcfirst(str_replace('unique', '', $name));
			return $this->callUnique($method, $arguments);
		}

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

	/**
	 * @param string $method
	 * @param array $arguments
	 *
	 * @return $this
	 */
	private function callNullable($method, array $arguments)
	{
		switch (true)
		{
			case in_array($method, $this->types):
				list($field, $callback) = array_pad($arguments, 2, null);
				$newArgs = [$field, $this->nullableFieldCallback($callback)];

				break;
			case ($method == 'field'):
				list($type, $field, $callback) = array_pad($arguments, 3, null);
				$newArgs = [$type, $field, $this->nullableFieldCallback($callback)];

				break;
			default:
				throw new \UnexpectedValueException("Method '$method' cannot be called with the 'nullable' modifier.");
		}

		return call_user_func_array([$this, $method], $newArgs);
	}

	/**
	 * @param string $method
	 * @param array $arguments
	 *
	 * @return $this
	 */
	private function callUnique($method, array $arguments)
	{
		switch (true)
		{
			case in_array($method, $this->types):
				list($field, $callback) = array_pad($arguments, 2, null);
				$newArgs = [$field, $this->uniqueFieldCallback($callback)];

				break;
			case ($method == 'field'):
				list($type, $field, $callback) = array_pad($arguments, 3, null);
				$newArgs = [$type, $field, $this->uniqueFieldCallback($callback)];

				break;
			default:
				throw new \UnexpectedValueException("Method '$method' cannot be called with the 'unique' modifier.");
		}

		return call_user_func_array([$this, $method], $newArgs);
	}

	/**
	 * @param callable|null $callback
	 * @return callable
	 */
	private function nullableFieldCallback(callable $callback = null)
	{
		return function (FieldBuilder $fieldBuilder) use ($callback){
			$fieldBuilder->nullable();

			if (is_callable($callback))
			{
				$callback($fieldBuilder);
			}
		};
	}

	/**
	 * @param callable|null $callback
	 * @return callable
	 */
	private function uniqueFieldCallback(callable $callback = null)
	{
		return function (FieldBuilder $fieldBuilder) use ($callback){
			$fieldBuilder->unique();

			if (is_callable($callback))
			{
				$callback($fieldBuilder);
			}
		};
	}

	/**
	 * @param string $field
	 * @param callable $callback
	 *
	 * @return callable
	 */
	private function makeMayBelongToCallback($field, callable $callback = null)
	{
		return function (Relations\BelongsTo $belongsTo) use ($field, $callback) {
			$belongsTo->keys(
				$this->namingStrategy->joinColumnName($field),
                $this->namingStrategy->referenceColumnName(),
				true
			);

			if ($callback)
			{
				$callback($belongsTo);
			}
		};
	}
}
