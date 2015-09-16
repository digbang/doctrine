<?php namespace Tests\Commands\Mappings;

use Digbang\Doctrine\Commands\Mappings\ConvertCommand;

class ConvertCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(ConvertCommand::class, new ConvertCommand());
    }
}
