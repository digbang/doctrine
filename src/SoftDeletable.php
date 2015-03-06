<?php namespace Digbang\Doctrine;

interface SoftDeletable
{
	/**
	 * Mark this entity as deleted.
	 *
	 * @return void
	 */
	public function markAsDeleted();

	/**
	 * @return \Carbon\Carbon
	 */
	public function getDeletedAt();

	/**
	 * @return boolean
	 */
	public function isDeleted();
}
