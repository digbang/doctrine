<?php namespace Digbang\Doctrine\Metadata;

interface EntityMapping
{
	/**
	 * Returns the fully qualified name of the entity that this mapper maps.
	 *
	 * @return string
	 */
	public function getEntityName();

	/**
	 * Load the entity's metadata through the Metadata Builder object.
	 *
	 * @param Builder $builder
	 * @return void
	 */
	public function build(Builder $builder);
}
