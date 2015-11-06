<?php namespace Digbang\Doctrine\Cache;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Cache\CollectionCacheKey;
use Doctrine\ORM\Cache\Logging\CacheLogger;
use Doctrine\ORM\Cache\Persister\CachedPersister;
use Doctrine\ORM\Cache\Persister\Collection\CachedCollectionPersister;
use Doctrine\ORM\Cache\Persister\Entity\AbstractEntityPersister;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;

trait EntityPersisterDecoratorTrait
{
	/**
	 * @return AbstractEntityPersister
	 */
	abstract protected function getPersister();

	/**
	 * @return UnitOfWork
	 */
	abstract protected function getUnitOfWork();

	/**
	 * @return ClassMetadataFactory
	 */
	abstract protected function getMetadataFactory();

	/**
	 * @return null|CacheLogger
	 */
	abstract protected function getCacheLogger();

	/**
	 * {@inheritdoc}
	 */
	public function loadManyToManyCollection(array $assoc, $sourceEntity, PersistentCollection $coll)
	{
		$uow = $this->getUnitOfWork();

		$persister = $uow->getCollectionPersister($assoc);
		$hasCache  = ($persister instanceof CachedPersister);
		$key       = null;

		if ($hasCache) {
			/** @var CachedCollectionPersister $persister */
			$ownerId = $uow->getEntityIdentifier($coll->getOwner());
			$key     = $this->buildCollectionCacheKey($assoc, $ownerId);
			$list    = $persister->loadCollectionCache($coll, $key);

			if ($list !== null) {
				if ($this->getCacheLogger()) {
					$this->getCacheLogger()->collectionCacheHit($persister->getCacheRegion()->getName(), $key);
				}

				return $list;
			}
		}

		$list = $this->getPersister()->loadManyToManyCollection($assoc, $sourceEntity, $coll);

		if ($hasCache) {
			$persister->storeCollectionCache($key, $list);

			if ($this->getCacheLogger()) {
				$this->getCacheLogger()->collectionCacheMiss($persister->getCacheRegion()->getName(), $key);
			}
		}

		return $list;
	}

	/**
	 * {@inheritdoc}
	 */
	public function loadOneToManyCollection(array $assoc, $sourceEntity, PersistentCollection $coll)
	{
		$uow = $this->getUnitOfWork();

		$persister = $uow->getCollectionPersister($assoc);
		$hasCache  = ($persister instanceof CachedPersister);

		if ($hasCache) {
			/** @var CachedCollectionPersister $persister */
			$ownerId = $uow->getEntityIdentifier($coll->getOwner());
			$key     = $this->buildCollectionCacheKey($assoc, $ownerId);
			$list    = $persister->loadCollectionCache($coll, $key);

			if ($list !== null) {
				if ($this->getCacheLogger()) {
					$this->getCacheLogger()->collectionCacheHit($persister->getCacheRegion()->getName(), $key);
				}

				return $list;
			}
		}

		$list = $this->getPersister()->loadOneToManyCollection($assoc, $sourceEntity, $coll);

		if ($hasCache) {
			$persister->storeCollectionCache($key, $list);

			if ($this->getCacheLogger()) {
				$this->getCacheLogger()->collectionCacheMiss($persister->getCacheRegion()->getName(), $key);
			}
		}

		return $list;
	}

	/**
	 * @param array $assoc
	 * @param array $ownerId
	 *
	 * @return CollectionCacheKey
	 */
	protected function buildCollectionCacheKey(array $assoc, $ownerId)
	{
		/** @var ClassMetadata $metadata */
		$metadata = $this->getMetadataFactory()->getMetadataFor($assoc['sourceEntity']);

		return new CollectionCacheKey($metadata->rootEntityName, $assoc['fieldName'], $ownerId);
	}
}
