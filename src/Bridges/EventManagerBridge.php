<?php namespace Digbang\Doctrine\Bridges;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\DBAL\Events as DBALEvents;
use Doctrine\ORM\Events;
use Illuminate\Contracts\Events\Dispatcher;

class EventManagerBridge extends EventManager
{
	/**
	 * @type \Illuminate\Events\Dispatcher;
	 */
	private $dispatcher;

	/**
	 * @type string[]
	 */
	private $doctrineEvents = [
		Events::preRemove,
		Events::postRemove,
		Events::prePersist,
		Events::postPersist,
		Events::preUpdate,
		Events::postUpdate,
		Events::postLoad,
		Events::loadClassMetadata,
		Events::onClassMetadataNotFound,
		Events::preFlush,
		Events::onFlush,
		Events::postFlush,
		Events::onClear,
		DBALEvents::postConnect,
		DBALEvents::onSchemaCreateTable,
		DBALEvents::onSchemaCreateTableColumn,
		DBALEvents::onSchemaDropTable,
		DBALEvents::onSchemaAlterTable,
		DBALEvents::onSchemaAlterTableAddColumn,
		DBALEvents::onSchemaAlterTableRemoveColumn,
		DBALEvents::onSchemaAlterTableChangeColumn,
		DBALEvents::onSchemaAlterTableRenameColumn,
		DBALEvents::onSchemaColumnDefinition,
		DBALEvents::onSchemaIndexDefinition
	];

	/**
	 * @param Dispatcher $dispatcher
	 */
	public function __construct(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	/**
	 * Dispatch (fire) an event through Laravel's event dispatcher.
	 *
	 * @param string|object  $eventName
	 * @param EventArgs|null $eventArgs
	 *
	 * @return void
	 */
	public function dispatchEvent($eventName, EventArgs $eventArgs = null)
	{
		$this->dispatcher->fire($eventName, $eventArgs);
	}

	/**
	 * @param string|null $event
	 * @return array
	 */
	public function getListeners($event = null)
	{
		if ($event === null)
		{
			return $this->dispatcher->getListeners('*');
		}

		return $this->dispatcher->getListeners($event);
	}

	/**
	 * @param string|object $event
	 * @return bool
	 */
	public function hasListeners($event)
	{
		return $this->dispatcher->hasListeners($event);
	}

	/**
	 * @param array|string $events
	 * @param object       $listener
	 */
	public function addEventListener($events, $listener)
	{
		foreach ((array) $events as $event)
		{
			$boundListener = $this->getDoctrineListener($listener, $event);

			$this->dispatcher->listen($event, $boundListener);
		}
	}

	/**
	 * @param array|string $events
	 * @param object       $listener
	 */
	public function removeEventListener($events, $listener)
	{
		foreach ((array) $events as $event)
		{
			$listeners = $this->dispatcher->getListeners($event);

			$boundListener = $this->getDoctrineListener($listener, $event);

			if (($key = array_search($boundListener, $listeners)) !== false)
			{
				unset($listeners[$key]);

				$this->dispatcher->forget($event);
				foreach ($listeners as $recoverListener)
				{
					$this->dispatcher->listen($event, $recoverListener);
				}
			}
		}
	}

	/**
	 * @param EventSubscriber $subscriber
	 */
	public function addEventSubscriber(EventSubscriber $subscriber)
	{
		$this->addEventListener($subscriber->getSubscribedEvents(), $subscriber);
	}

	/**
	 * @param EventSubscriber $subscriber
	 */
	public function removeEventSubscriber(EventSubscriber $subscriber)
	{
		$this->removeEventListener($subscriber->getSubscribedEvents(), $subscriber);
	}

	/**
	 * @param string $eventName
	 * @return bool
	 */
	private function isDoctrineEvent($eventName)
	{
		return in_array($eventName, $this->doctrineEvents);
	}

	/**
	 * @param mixed $listener
	 * @param string $eventName
	 *
	 * @return callable|string
	 */
	private function getDoctrineListener($listener, $eventName)
	{
		if ($this->isDoctrineEvent($eventName))
		{
			return [$listener, $eventName];
		}

		return $listener;
	}
}
