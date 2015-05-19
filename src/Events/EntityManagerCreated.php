<?php namespace Digbang\Doctrine\Events;

use Doctrine\Common\EventArgs;
use Doctrine\ORM\EntityManagerInterface;

class EntityManagerCreated extends EventArgs
{
	/**
	 * @type EntityManagerInterface
	 */
	private $entityManager;

	/**
	 * @param EntityManagerInterface $entityManager
	 */
	public function __construct(EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
	}

	/**
	 * @return EntityManagerInterface
	 */
	public function getEntityManager()
	{
		return $this->entityManager;
	}
}
