<?php namespace Tests\Commands\Schema;

use Digbang\Doctrine\Commands\Schema\UpdateCommand;

class UpdateCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(UpdateCommand::class, new UpdateCommand());
    }
}
