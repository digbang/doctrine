<?php namespace Digbang\Doctrine\Bridges;

use Doctrine\Common\EventArgs;
use Doctrine\Common\EventManager;
use Doctrine\Common\EventSubscriber;
use Illuminate\Events\Dispatcher;

class EventManagerBridge extends EventManager
{
	private $dispatcher;

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
		$this->dispatcher->listen($events, $listener);
	}

	public function removeEventListener($events, $listener)
	{
		foreach ((array) $events as $event)
		{
			$listeners = $this->dispatcher->getListeners($event);

			if (($key = array_search($listener, $listeners)) !== false)
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
		$this->dispatcher->listen($subscriber->getSubscribedEvents(), $subscriber);
	}

	public function removeEventSubscriber(EventSubscriber $subscriber)
	{
		$this->removeEventListener($subscriber->getSubscribedEvents(), $subscriber);
	}
}
