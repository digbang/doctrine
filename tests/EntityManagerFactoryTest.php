<?php namespace Tests;

use Digbang\Doctrine\Bridges\DatabaseConfigurationBridge;
use Digbang\Doctrine\EntityManagerFactory;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\EventManager;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\NamingStrategy;
use Illuminate\Contracts\Config\Repository;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class EntityManagerFactoryTest extends TestCase
{
	/** @test */
	function it_should_create_entity_managers()
	{
		/** @type Repository|Mock $config */
		$config = $this->getEmptyMock(Repository::class);
		/** @type CacheProvider|Mock $cache */
		$cache = $this->getEmptyMock(CacheProvider::class);
		/** @type DatabaseConfigurationBridge|Mock $dbConfig */
		$dbConfig = $this->getEmptyMock(DatabaseConfigurationBridge::class);
		/** @type NamingStrategy|Mock $namingStrategy */
		$namingStrategy = $this->getEmptyMock(NamingStrategy::class);
		/** @type MappingDriver|Mock $mappingDriver */
		$mappingDriver = $this->getEmptyMock(MappingDriver::class);
		/** @type EventManager|Mock $eventManager */
		$eventManager = $this->getEmptyMock(EventManager::class);

		$dbConfig->expects($this->once())->method('getConnection')
			->willReturn([
				'driver'   => 'pdo_sqlite',
				'path'     => __DIR__ . '/database.sqlite',
				'user'     => null,
				'password' => null,
				'charset'  => 'utf8'
			]);

		$eventManager->expects($this->atLeast(2))->method('dispatchEvent');

		$emf = new EntityManagerFactory($config, $cache, $dbConfig, $namingStrategy, $mappingDriver, $eventManager);

		$entityManager = $emf->create();

		$this->assertInstanceOf(EntityManager::class, $entityManager);
	}
}