<?php namespace Digbang\Doctrine\Cache;

use Doctrine\ORM\Cache\DefaultCacheFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Persisters\Entity\EntityPersister;

class DecoratedCacheFactory extends DefaultCacheFactory
{
    /**
     * {@inheritdoc}
     */
    public function buildCachedEntityPersister(EntityManagerInterface $em, EntityPersister $persister, ClassMetadata $metadata)
    {
        $region     = $this->getRegion($metadata->cache);
        $usage      = $metadata->cache['usage'];

        if ($usage === ClassMetadata::CACHE_USAGE_READ_ONLY) {
            return new ReadOnlyCachedEntityPersisterDecorator($persister, $region, $em, $metadata);
        }

        if ($usage === ClassMetadata::CACHE_USAGE_NONSTRICT_READ_WRITE) {
            return new NonStrictReadWriteCachedEntityPersisterDecorator($persister, $region, $em, $metadata);
        }

        if ($usage === ClassMetadata::CACHE_USAGE_READ_WRITE) {
            return new ReadWriteCachedEntityPersisterDecorator($persister, $region, $em, $metadata);
        }

        throw new \InvalidArgumentException(sprintf("Unrecognized access strategy type [%s]", $usage));
    }
}
