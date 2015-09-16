<?php namespace Tests\Commands\Exec;

use Digbang\Doctrine\Commands\Exec\RunSqlCommand;

class RunSqlCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(RunSqlCommand::class, new RunSqlCommand());
    }
}
