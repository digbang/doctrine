<?php namespace Tests\Metadata;

use Digbang\Doctrine\Laravel\LaravelNamingStrategy;
use Digbang\Doctrine\Metadata\Builder;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\Mapping\NamingStrategy;
use Illuminate\Support\Str;
use Tests\Fixtures\EmailIdentityEntity;
use Tests\TestCase;

class BuilderTest extends TestCase
{
	/**
	 * @type Builder
	 */
	private $builder;

	/**
	 * @type ClassMetadataInfo
	 */
	private $metadata;

	/**
	 * @type array
	 */
	private $types;

	/**
	 * @type NamingStrategy
	 */
	private $namingStrategy;

	public function setUp()
	{
		$this->namingStrategy = new LaravelNamingStrategy(new Str);
		$this->builder = $this->newBuilder();

		$ref = new \ReflectionProperty($this->builder, 'types');
		$ref->setAccessible(true);
		$this->types = $ref->getValue($this->builder);
	}

	private function newBuilder()
	{
		return new Builder(
			new ClassMetadataBuilder(
				$this->metadata = new ClassMetadataInfo(EmailIdentityEntity::class, $this->namingStrategy)
			),
			$this->namingStrategy
		);
	}

	public function doMagicMethodForAllTypes($prefix, $args, $assertion)
	{
		foreach ($this->types as $type)
		{
			$func = $prefix . ucfirst($type);

			call_user_func_array([$this->newBuilder(), $func], $args);

			$assertion();
		}
	}

	/** @test */
	public function it_should_allow_a_nullable_prefix_on_all_field_types()
	{
		$this->doMagicMethodForAllTypes('nullable', ['field'], function(){
			$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
		});
	}

	/** @test */
	public function it_shouldnt_allow_other_nullable_cases()
	{
		$this->setExpectedException(\UnexpectedValueException::class);

		$this->builder->nullableBelongsTo('foo');
		$this->builder->nullableEmbeddable('SomeClass', 'someClass');
	}

	/** @test */
	public function it_should_allow_required_many_to_one_relations()
	{
		$this->builder->belongsTo('SomeEntity', 'someEntity');

		$this->assertFalse($this->metadata->associationMappings['someEntity']['joinColumns'][0]['nullable']);
	}

	/** @test */
	public function it_should_allow_optional_many_to_one_relations()
	{
		$this->builder->mayBelongTo('SomeEntity', 'someEntity');

		$this->assertTrue($this->metadata->associationMappings['someEntity']['joinColumns'][0]['nullable']);
	}

	/** @test */
	public function it_should_allow_a_unique_prefix_in_all_field_types()
	{
		$this->doMagicMethodForAllTypes('unique', ['field'], function(){
			$this->assertTrue($this->metadata->fieldMappings['field']['unique']);
		});
	}

	/** @test */
	public function it_shouldnt_allow_other_unique_cases()
	{
		$this->setExpectedException(\UnexpectedValueException::class);

		$this->builder->uniqueBelongsTo('foo');
		$this->builder->uniqueEmbeddable('SomeClass', 'someClass');
	}
}
