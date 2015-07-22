<?php namespace Digbang\Doctrine\Listeners;

use Digbang\Doctrine\SoftDeletable;
use Doctrine\ORM\Event\OnFlushEventArgs;

class SoftDeletableListener
{
	/**
	 * This method will take all entities scheduled for deletion that
	 * implement the SoftDeletable interface and, if they are not deleted yet,
	 * will mark them as deleted and persist them again.
	 *
	 * @param OnFlushEventArgs $event
	 */
	public function onFlush(OnFlushEventArgs $event)
	{
		$entityManager = $event->getEntityManager();
		$unitOfWork    = $entityManager->getUnitOfWork();

		$notDeleted = $this->filterNotDeletedSoftDeleteables(
			$unitOfWork->getScheduledEntityDeletions()
		);

		foreach ($notDeleted as $entity)
		{
			/** @type SoftDeletable $entity */
			$entity->markAsDeleted();
			$entityManager->persist($entity);

			$now = $entity->getDeletedAt();

			$unitOfWork->propertyChanged($entity, 'deletedAt', null, $now);
			$unitOfWork->scheduleExtraUpdate($entity, [
				'deletedAt' => [null, $now]
			]);
		}
	}

	private function filterNotDeletedSoftDeleteables(array $entities)
	{
		return array_filter($entities, function($entity){
			if ($entity instanceof SoftDeletable)
			{
				return ! $entity->isDeleted();
			}

			return false;
		});
	}
}
