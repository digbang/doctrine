<?php namespace Digbang\Doctrine\Listeners;

use Digbang\Doctrine\SoftDeleteTrait;

class SoftDeletableListener extends \Mitch\LaravelDoctrine\EventListeners\SoftDeletableListener
{
    private function isSoftDeletable($entity)
    {
        return array_key_exists(SoftDeleteTrait::class, class_uses($entity));
    }
}
