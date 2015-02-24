<?php namespace Digbang\Doctrine;

trait SoftDeleteTrait
{
    /**
     * @type \Carbon\Carbon
     */
	private $deletedAt;
}
