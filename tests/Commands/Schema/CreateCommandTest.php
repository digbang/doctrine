<?php namespace Tests\Commands\Schema;

use Digbang\Doctrine\Commands\Schema\CreateCommand;

class CreateCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(CreateCommand::class, new CreateCommand());
    }
}
