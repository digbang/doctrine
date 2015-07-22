<?php namespace Digbang\Doctrine\Bridges;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Illuminate\Contracts\Events\Dispatcher;

class EventManagerBridge extends EventManager
{
	private $dispatcher;

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
	];

	public function __construct(Dispatcher $dispatcher)
	{
		$this->dispatcher = $dispatcher;
	}

	public function dispatchEvent($eventName, EventArgs $eventArgs = null)
	{
		$this->dispatcher->fire($eventName, $eventArgs);
	}

	public function getListeners($event = null)
	{
		if ($event === null)
		{
			return $this->dispatcher->getListeners('*');
		}

		return $this->dispatcher->getListeners($event);
	}

	public function hasListeners($event)
	{
		return $this->dispatcher->hasListeners($event);
	}

	public function addEventListener($events, $listener)
	{
		foreach ((array) $events as $event)
		{
			$boundListener = $this->getDoctrineListener($listener, $event);

			$this->dispatcher->listen($event, $boundListener);
		}
	}

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

	public function addEventSubscriber(EventSubscriber $subscriber)
	{
		$this->addEventListener($subscriber->getSubscribedEvents(), $subscriber);
	}

	public function removeEventSubscriber(EventSubscriber $subscriber)
	{
		$this->removeEventListener($subscriber->getSubscribedEvents(), $subscriber);
	}

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
