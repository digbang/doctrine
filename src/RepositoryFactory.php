<?php namespace Digbang\Doctrine;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Repository\RepositoryFactory as RepositoryFactoryInterface;

class RepositoryFactory implements RepositoryFactoryInterface
{
	/**
	 * @type Container
	 */
	private $container;

	/**
	 * @type Repository
	 */
	private $config;

	public function __construct(Repository $config, Container $container)
	{
		$this->config = $config;
		$this->container = $container;
	}

	/**
	 * Gets the repository for an entity class.
	 *
	 * @param \Doctrine\ORM\EntityManagerInterface $entityManager The EntityManager instance.
	 * @param string                               $entityName    The name of the entity.
	 *
	 * @return \Doctrine\Common\Persistence\ObjectRepository
	 */
	public function getRepository(EntityManagerInterface $entityManager, $entityName)
	{
		$namespace = $this->config->get('doctrine-repositories.namespace');
		$suffix = $this->config->get('doctrine-repositories.suffix', '');
		$basename = class_basename($entityName);

		return $this->container->make("$namespace\\{$basename}Repository{$suffix}");
	}
}
 