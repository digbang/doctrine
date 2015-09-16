<?php namespace Tests\Commands\Generate;

use Digbang\Doctrine\Commands\Generate\ProxiesCommand;

class ProxiesCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(ProxiesCommand::class, new ProxiesCommand());
    }
}
