<?php namespace Digbang\Doctrine\Cache;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Cache\Logging\CacheLogger;
use Doctrine\ORM\Cache\Persister\Entity\AbstractEntityPersister;
use Doctrine\ORM\Cache\Persister\Entity\ReadWriteCachedEntityPersister;
use Doctrine\ORM\UnitOfWork;

class ReadWriteCachedEntityPersisterDecorator extends ReadWriteCachedEntityPersister
{
	use EntityPersisterDecoratorTrait;

	/**
	 * @return AbstractEntityPersister
	 */
	protected function getPersister()
	{
		return $this->persister;
	}

	/**
	 * @return UnitOfWork
	 */
	protected function getUnitOfWork()
	{
		return $this->uow;
	}

	/**
	 * @return ClassMetadataFactory
	 */
	protected function getMetadataFactory()
	{
		return $this->metadataFactory;
	}

	/**
	 * @return null|CacheLogger
	 */
	protected function getCacheLogger()
	{
		return $this->cacheLogger;
	}
}
