<?php namespace Tests\Commands\Mappings;

use Digbang\Doctrine\Commands\Mappings\InfoCommand;

class InfoCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(InfoCommand::class, new InfoCommand());
    }
}
