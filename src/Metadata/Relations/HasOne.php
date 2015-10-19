<?php namespace Digbang\Doctrine\Metadata\Relations;

use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;

/**
 * Class HasOne
 *
 * @package Digbang\Doctrine\Metadata\Relations
 * @method $this mappedBy($fieldName)
 */
class HasOne extends Relation
{
	/**
	 * @param ClassMetadataBuilder $metadataBuilder
	 * @param string               $entityName
	 * @param string               $relation
	 */
	public function __construct(ClassMetadataBuilder $metadataBuilder, $entityName, $relation)
	{
		parent::__construct(
			$metadataBuilder->createOneToOne($relation, $entityName),
			$metadataBuilder->getClassMetadata(),
			$relation
		);
	}
}
