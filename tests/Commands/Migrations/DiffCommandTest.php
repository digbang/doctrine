<?php namespace Tests\Commands\Migrations;

use Digbang\Doctrine\Commands\Migrations\DiffCommand;

class DiffCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(DiffCommand::class, new DiffCommand());
    }
}
