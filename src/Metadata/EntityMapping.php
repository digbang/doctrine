<?php namespace Digbang\Doctrine\Metadata;

interface EntityMapping
{
	public function build(Builder $builder);
}
