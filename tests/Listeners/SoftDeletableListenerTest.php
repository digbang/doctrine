<?php namespace Tests\Listeners;

use Digbang\Doctrine\Listeners\SoftDeletableListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\UnitOfWork;
use Tests\Fixtures\IntIdentityEntity;
use Tests\TestCase;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class SoftDeletableListenerTest extends TestCase
{
	/**
	 * @type Mock
	 */
	private $em;

	/** @test */
	function it_should_detect_our_interface()
    {
	    $softDeletable = new IntIdentityEntity('a', 'b');
	    $listener = $this->setUpFor($softDeletable);

	    $this->em->expects($this->once())->method('persist')->with($softDeletable);

	    $this->flushEvent($listener);
    }

    /** @test */
	function it_should_not_detect_mitchells_trait()
    {
	    $softDeletable = new FakeEntityThatImplementsMitchellsStuff();
	    $listener = $this->setUpFor($softDeletable);

	    $this->em->expects($this->never())->method('persist')->with($softDeletable);

	    $this->flushEvent($listener);
    }

	/**
	 * @param object $softDeletable
	 *
	 * @return SoftDeletableListener
	 */
	private function setUpFor($softDeletable)
	{
		$listener = new SoftDeletableListener;

		$this->em  = $this->getEmptyMock(EntityManagerInterface::class);
		$uow = $this->getEmptyMock(UnitOfWork::class);

		$this->em->expects($this->any())->method('getUnitOfWork')->willReturn($uow);

		$uow->expects($this->once())->method('getScheduledEntityDeletions')->willReturn([$softDeletable]);

		return $listener;
	}

	private function flushEvent(SoftDeletableListener $listener)
	{
		$event = new OnFlushEventArgs($this->em);

		$listener->onFlush($event);
	}
}

class FakeEntityThatImplementsMitchellsStuff
{
    use \Mitch\LaravelDoctrine\Traits\SoftDeletes;
}