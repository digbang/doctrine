<?php namespace Digbang\Doctrine\Metadata;

interface EmbeddableMapping
{
	/**
	 * Returns the fully qualified name of the embeddable that this mapper maps.
	 *
	 * @return string
	 */
	public function getEmbeddableName();

	/**
	 * Load the embeddable's metadata through the Metadata Builder object.
	 *
	 * @param Builder $builder
	 * @return void
	 */
	public function build(Builder $builder);
}
