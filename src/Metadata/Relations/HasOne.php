<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Class HasOne
 *
 * @package Digbang\Doctrine\Metadata\Relations
 * @method $this inversedBy($fieldName)
 */
class HasOne extends Relation
{
	public function __construct(ClassMetadataBuilder $metadataBuilder, $entityName, $relation)
	{
		$this->associationBuilder = $metadataBuilder->createOneToOne($relation, $entityName);
	}
}
