<?php namespace Tests\Metadata;

use Digbang\Doctrine\LaravelNamingStrategy;
use Digbang\Doctrine\Metadata\Builder;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
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

	public function setUp()
	{
		$namingStrategy = new LaravelNamingStrategy(new Str);

		$this->builder = new Builder(
			new ClassMetadataBuilder(
				$this->metadata = new ClassMetadataInfo(EmailIdentityEntity::class, $namingStrategy)
			),
			$namingStrategy
		);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_bigints_easily()
	{
		$this->builder->nullableBigint('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_booleans_easily()
	{
		$this->builder->nullableBoolean('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_datetimes_easily()
	{
		$this->builder->nullableDatetime('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_datetimetzs_easily()
	{
		$this->builder->nullableDatetimetz('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_dates_easily()
	{
		$this->builder->nullableDate('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_times_easily()
	{
		$this->builder->nullableTime('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_decimals_easily()
	{
		$this->builder->nullableDecimal('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_integers_easily()
	{
		$this->builder->nullableInteger('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_objects_easily()
	{
		$this->builder->nullableObject('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_smallints_easily()
	{
		$this->builder->nullableSmallint('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_strings_easily()
	{
		$this->builder->nullableString('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_texts_easily()
	{
		$this->builder->nullableText('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_binaries_easily()
	{
		$this->builder->nullableBinary('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_blobs_easily()
	{
		$this->builder->nullableBlob('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_floats_easily()
	{
		$this->builder->nullableFloat('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_guids_easily()
	{
		$this->builder->nullableGuid('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_tsvectors_easily()
	{
		$this->builder->nullableTsvector('field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_should_have_a_magic_way_to_make_nullable_fields_easily()
	{
		$this->builder->nullableField('my_custom_type', 'field');

		$this->assertTrue($this->metadata->fieldMappings['field']['nullable']);
	}

	/** @test */
	public function it_shouldnt_allow_other_nullable_cases()
	{
		$this->setExpectedException(\UnexpectedValueException::class);

		$this->builder->nullableBelongsTo('foo');
		$this->builder->nullableEmbeddable('SomeClass', 'someClass');
	}

	/** @test */
	public function it_should_allow_optional_many_to_one_relations()
	{
		$this->builder->mayBelongTo('SomeEntity', 'someEntity');

		$this->assertTrue($this->metadata->associationMappings['someEntity']['joinColumns'][0]['nullable']);
	}
}
