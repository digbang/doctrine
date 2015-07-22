<?php namespace Tests;

use Digbang\Doctrine\DoctrineUserProvider;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\ClassMetadata;
use Illuminate\Contracts\Hashing\Hasher;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class DoctrineUserProviderTest extends TestCase
{
	/**
	 * @type Mock
	 */
	private $hasherMock;

	/**
	 * @type Mock
	 */
	private $entityManagerMock;

	/**
	 * @type Mock
	 */
	private $repositoryMock;

	/**
	 * @type Mock
	 */
	private $classMetadataMock;

	public function setUp()
	{
		$this->hasherMock        = $this->getEmptyMock(Hasher::class);
		$this->entityManagerMock = $this->getEmptyMock(EntityManager::class);
		$this->repositoryMock    = $this->getEmptyMock(ObjectRepository::class);
		$this->classMetadataMock = $this->getEmptyMock(ClassMetadata::class);
	}

	/** @test */
	public function int_identity_should_not_call_the_entity_constructor()
	{
		$userProvider = $this->setUpIntIdentity();

		$this->classMetadataMock->identifier = ['id'];

		$this->repositoryMock
			->expects($this->once())
			->method('findOneBy')
			->with([
				'id' => 'THE_ID',
				'rememberToken' => 'THE_TOKEN'
			])
			->willReturn(null);

		$userProvider->retrieveByToken('THE_ID', 'THE_TOKEN');
	}

	/** @test */
	public function email_identity_should_not_call_the_entity_constructor()
	{
		$userProvider = $this->setUpEmailIdentity();

		$this->classMetadataMock->identifier = ['email'];

		$this->repositoryMock
			->expects($this->once())
			->method('findOneBy')
			->with([
				'email' => 'THE_ID',
				'rememberToken' => 'THE_TOKEN'
			])
			->willReturn(null);

		$userProvider->retrieveByToken('THE_ID', 'THE_TOKEN');
	}

	/**
	 * @return DoctrineUserProvider
	 */
	private function setUpIntIdentity()
	{
		return $this->setUpWith(Fixtures\IntIdentityEntity::class);
	}

	/**
	 * @return DoctrineUserProvider
	 */
	private function setUpEmailIdentity()
	{
		return $this->setUpWith(Fixtures\EmailIdentityEntity::class);
	}

	/**
	 * @param $entityName
	 *
	 * @return DoctrineUserProvider
	 */
	private function setUpWith($entityName)
	{
		$this->entityManagerMock
			->expects($this->any())
			->method('getRepository')
			->with($entityName)
			->willReturn($this->repositoryMock);

		$this->entityManagerMock
			->expects($this->any())
			->method('getClassMetadata')
			->with($entityName)
			->willReturn($this->classMetadataMock);

		return new DoctrineUserProvider(
			$this->hasherMock,
			$this->entityManagerMock,
			$entityName
		);
	}
}
