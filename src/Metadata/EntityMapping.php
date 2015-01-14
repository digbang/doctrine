<?php namespace Digbang\Doctrine\Metadata;

interface EntityMapping
{
	/**
	 * Load the entity's metadata through the Metadata Builder object.
	 *
	 * @param Builder $builder
	 * @return void
	 */
	public function build(Builder $builder);

	/**
	 * Returns the fully qualified name of the entity that this mapper maps.
	 * This needs to be static so that we can load all entity mapping names
	 * without instantiating all EntityMapping classes.
	 *
	 * @return string
	 */
	public static function getEntityName();
}
