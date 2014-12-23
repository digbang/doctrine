<?php namespace Digbang\Doctrine\Metadata;

use Digbang\Doctrine\Metadata\Relations\BelongsTo;
use Digbang\Doctrine\Metadata\Relations\BelongsToMany;
use Digbang\Doctrine\Metadata\Relations\HasMany;
use Digbang\Doctrine\Metadata\Relations\HasOne;
use Digbang\Doctrine\Metadata\Relations\RelationInterface;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\Builder\FieldBuilder;
use Illuminate\Support\Str;

class Builder 
{
	/**
	 * @type \Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder
	 */
	private $metadataBuilder;

	/**
	 * @type \Illuminate\Support\Str
	 */
	private $str;

	function __construct(ClassMetadataBuilder $metadataBuilder, Str $str)
	{
		$this->metadataBuilder = $metadataBuilder;
		$this->str = $str;
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
	public function primary($name = 'id', $type = 'integer')
	{
		return $this->field($name, $type, function(FieldBuilder $fieldBuilder) use ($type) {
			$fieldBuilder->isPrimaryKey();

			if ('integer' == $type)
			{
				$fieldBuilder->generatedValue();
			}
		});
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function bigint($name)
	{
		return $this->field($name, Type::BIGINT);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function boolean($name)
	{
		return $this->field($name, Type::BOOLEAN);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function datetime($name)
	{
		return $this->field($name, Type::DATETIME);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function datetimetz($name)
	{
		return $this->field($name, Type::DATETIMETZ);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function date($name)
	{
		return $this->field($name, Type::DATE);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function time($name)
	{
		return $this->field($name, Type::TIME);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function decimal($name)
	{
		return $this->field($name, Type::DECIMAL);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function integer($name)
	{
		return $this->field($name, Type::INTEGER);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function object($name)
	{
		return $this->field($name, Type::OBJECT);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function smallint($name)
	{
		return $this->field($name, Type::SMALLINT);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function string($name)
	{
		return $this->field($name, Type::STRING);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function text($name)
	{
		return $this->field($name, Type::TEXT);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function binary($name)
	{
		return $this->field($name, Type::BINARY);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function blob($name)
	{
		return $this->field($name, Type::BLOB);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function float($name)
	{
		return $this->field($name, Type::FLOAT);
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function guid($name)
	{
		return $this->field($name, Type::GUID);
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
		$this->metadataBuilder->addLifecycleEvent('onPrePersist', 'prePersist');

		return $this->datetime('createdAt');
	}

	/**
	 * @return $this
	 */
	public function updatedAt()
	{
		$this->metadataBuilder->addLifecycleEvent('onPreUpdate', 'preUpdate');

		return $this->datetime('updatedAt');
	}

	/**
	 * @return $this
	 */
	public function deletedAt()
	{
		return $this->datetime('deletedAt');
	}

	/**
	 * @param string $name
	 * @param string $type
	 * @param callable|null $callback
	 *
	 * @return $this
	 */
	public function field($name, $type, \Closure $callback = null)
	{
		$fieldBuilder = $this->metadataBuilder
			->createField($name, $type);

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
	 * @param $typeColumn
	 *
	 * @return $this
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
	public function belongsTo($entityName, $relation, \Closure $callback = null)
	{
		return $this->addRelation(
			new BelongsTo($this->metadataBuilder, $entityName, $relation),
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
	public function belongsToMany($entityName, $relation, \Closure $callback = null)
	{
		return $this->addRelation(
			new BelongsToMany($this->metadataBuilder, $entityName, $relation),
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
	public function hasMany($entityName, $relation, \Closure $callback = null)
	{
		return $this->addRelation(
			new HasMany($this->metadataBuilder, $entityName, $relation),
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
	public function hasOne($entityName, $relation, \Closure $callback = null)
	{
		return $this->addRelation(
			new HasOne($this->metadataBuilder, $entityName, $relation),
			$callback
		);
	}

	/**
	 * Adds a custom relation to the entity.
	 * The relation only needs to implement the RelationInterface
	 * @param \Digbang\Doctrine\Metadata\Relations\RelationInterface $relation
	 * @param callable|null                                          $callback
	 *
	 * @return $this
	 */
	public function addRelation(RelationInterface $relation, \Closure $callback = null)
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
	 */
	public function getMetadataBuilder()
	{
		return $this->metadataBuilder;
	}
}
