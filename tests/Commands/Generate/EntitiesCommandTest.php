<?php namespace Tests\Commands\Generate;

use Digbang\Doctrine\Commands\Generate\EntitiesCommand;

class EntitiesCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(EntitiesCommand::class, new EntitiesCommand());
    }
}
