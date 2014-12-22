<?php namespace Digbang\Doctrine;

trait TimestampsTrait
{
	/**
	 * @type \DateTime
	 */
	private $createdAt;

	/**
	 * @type \DateTime
	 */
	private $updatedAt;

	public function onPrePersist()
	{
		$now = new \DateTime();

		$this->createdAt = $now;
		$this->updatedAt = $now;
	}

	public function onPreUpdate()
	{
		$now = new \DateTime();

		$this->updatedAt = $now;
	}
}
