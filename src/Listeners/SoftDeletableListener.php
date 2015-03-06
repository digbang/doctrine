<?php namespace Digbang\Doctrine\Listeners;

use Digbang\Doctrine\SoftDeletable;
use Doctrine\ORM\Event\OnFlushEventArgs;

class SoftDeletableListener
{
	public function onFlush(OnFlushEventArgs $event)
	{
		$entityManager = $event->getEntityManager();
		$unitOfWork    = $entityManager->getUnitOfWork();

		foreach ($unitOfWork->getScheduledEntityDeletions() as $entity)
		{
			if ($entity instanceof SoftDeletable)
			{
				if ($entity->isDeleted())
				{
					continue;
				}

				$entity->markAsDeleted();
				$entityManager->persist($entity);

				$now = $entity->getDeletedAt();

				$unitOfWork->propertyChanged($entity, 'deletedAt', null, $now);
				$unitOfWork->scheduleExtraUpdate(
					$entity,
					[
						'deletedAt' => [null, $now]
					]
				);
			}
		}
	}
}
