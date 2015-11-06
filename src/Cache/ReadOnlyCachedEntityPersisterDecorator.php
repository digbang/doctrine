<?php namespace Digbang\Doctrine\Cache;

use Doctrine\ORM\Cache\CacheException;
use Doctrine\Common\Util\ClassUtils;

class ReadOnlyCachedEntityPersisterDecorator extends NonStrictReadWriteCachedEntityPersisterDecorator
{
	/**
	 * {@inheritdoc}
	 */
	public function update($entity)
	{
		throw CacheException::updateReadOnlyEntity(ClassUtils::getClass($entity));
	}
}
