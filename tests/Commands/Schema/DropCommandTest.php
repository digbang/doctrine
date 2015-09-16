<?php namespace Tests\Commands\Schema;

use Digbang\Doctrine\Commands\Schema\DropCommand;

class DropCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(DropCommand::class, new DropCommand());
    }
}
