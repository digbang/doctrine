<?php namespace Tests\Metadata;

use Digbang\Doctrine\LaravelNamingStrategy;
use Digbang\Doctrine\Metadata\DecoupledMappingDriver;
use Doctrine\Instantiator\Instantiator;
use Doctrine\Instantiator\InstantiatorInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Illuminate\Config\Repository;
use Illuminate\Support\Str;
use Tests\Fixtures\IntIdentityEntity;
use Tests\Fixtures\Mappings\FakeClassMapping;

class DecoupledMappingDriverTest extends \PHPUnit_Framework_TestCase
{
	/** @test */
	public function it_should_instantiate_entity_mapping_classes_based_on_configuration()
	{
		$config = $this->getMock(Repository::class, ['get']);

		$config->expects($this->any())->method('get')
			->will($this->returnValueMap([
				['doctrine-mappings.entities', [], [FakeClassMapping::class]],
				['doctrine-mappings.embeddables', [], []],
			]));

		$mapping = $this->getMock(FakeClassMapping::class, ['build']);

		$instantiator = $this->getMock(InstantiatorInterface::class, ['instantiate']);
		$instantiator
			->expects($this->once())
			->method('instantiate')
			->with(FakeClassMapping::class)
			->willReturn($mapping);

		$mapping
			->expects($this->once())
			->method('build');

		$namingStrategy = new LaravelNamingStrategy(new Str);

		/** @type Repository $config */
		/** @type Instantiator $instantiator */
		$driver = new DecoupledMappingDriver($config, $namingStrategy, $instantiator);
		$driver->loadMetadataForClass(
			IntIdentityEntity::class,
			new ClassMetadataInfo(IntIdentityEntity::class, $namingStrategy)
		);
	}
}
