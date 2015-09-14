<?php namespace Tests\Metadata;

use Digbang\Doctrine\Laravel\LaravelNamingStrategy;
use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Doctrine\Common\Persistence\Mapping\MappingException;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Illuminate\Support\Str;
use Tests\Fixtures\IntIdentityEntity;
use Tests\Fixtures\Mappings\FakeClassMapping;

class DecoupledMappingDriverTest extends \PHPUnit_Framework_TestCase
{
	/** @test */
	public function it_should_load_metadata_for_classes_that_were_added_to_it()
	{
		$namingStrategy = new LaravelNamingStrategy(new Str);

		$driver = new DecoupledMappingDriver($namingStrategy);
		$driver->addMapping(new FakeClassMapping);
		$driver->loadMetadataForClass(
			IntIdentityEntity::class,
			new ClassMetadataInfo(IntIdentityEntity::class, $namingStrategy)
		);
	}

	/** @test */
	public function it_should_fail_when_asked_for_metadata_that_was_not_added_to_it()
	{
		$namingStrategy = new LaravelNamingStrategy(new Str);

		$driver = new DecoupledMappingDriver($namingStrategy);

		$this->setExpectedException(MappingException::class);
		$driver->loadMetadataForClass(
			IntIdentityEntity::class,
			new ClassMetadataInfo(IntIdentityEntity::class, $namingStrategy)
		);
	}
}
