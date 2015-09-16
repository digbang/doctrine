<?php namespace Tests\Commands\Migrations;

use Digbang\Doctrine\Commands\Migrations\ExecuteCommand;

class ExecuteCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(ExecuteCommand::class, new ExecuteCommand());
    }
}
