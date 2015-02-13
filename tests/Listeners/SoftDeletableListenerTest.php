<?php namespace Tests\Listeners;

use Tests\Fixtures\Entity;

class SoftDeletableListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @test */
	function it_should_check_for_our_trait_instead_of_mitchells_trait()
    {
        $listener = new \Digbang\Doctrine\Listeners\SoftDeletableListener;

        $ref = new \ReflectionObject($listener);
        $privateMethod = $ref->getMethod('isSoftDeletable');
        $privateMethod->setAccessible(true);

        $softDeletable = new Entity();
        $nonSd = new FakeEntityThatImplementsMitchellsStuff();

        $this->assertTrue($privateMethod->invoke($listener, $softDeletable));
        $this->assertFalse($privateMethod->invoke($listener, $nonSd));
    }
}

class FakeEntityThatImplementsMitchellsStuff
{
    use \Mitch\LaravelDoctrine\Traits\SoftDeletes;
}