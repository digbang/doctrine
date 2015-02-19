<?php namespace Digbang\Doctrine\Filters;

use Digbang\Doctrine\SoftDeleteTrait;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class TrashedFilter extends SQLFilter
{
    public function addFilterConstraint(ClassMetadata $metadata, $table)
    {
        return $this->isSoftDeletable($metadata->rootEntityName)
            ? "{$table}.deleted_at IS NULL OR CURRENT_TIMESTAMP < {$table}.deleted_at"
            : '';
    }

    private function isSoftDeletable($entity)
    {
        return array_key_exists(SoftDeleteTrait::class, class_uses($entity));
    }
}
