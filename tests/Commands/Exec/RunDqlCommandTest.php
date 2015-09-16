<?php namespace Tests\Commands\Exec;

use Digbang\Doctrine\Commands\Exec\RunDqlCommand;

class RunDqlCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(RunDqlCommand::class, new RunDqlCommand());
    }
}
