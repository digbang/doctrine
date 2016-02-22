<?php namespace Digbang\Doctrine;

use Carbon\Carbon;

trait SoftDeleteTrait
{
    /**
     * @type \Carbon\Carbon
     */
	private $deletedAt;

	/**
	 * Mark this entity as deleted.
	 *
	 * @return void
	 */
	public function markAsDeleted()
	{
		$this->deletedAt = new Carbon;
	}

	/**
	 * @return \Carbon\Carbon
	 */
	public function getDeletedAt()
	{
		return $this->deletedAt;
	}

	/**
	 * @return boolean
	 */
	public function isDeleted()
	{
		return $this->deletedAt !== null;
	}
	
	/**
	 * Restore the entity. Mark it as not deleted.
	 *
	 * @return void
	 */
	public function restore()
	{
		$this->deletedAt = null;
	}
}
