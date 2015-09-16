<?php namespace Tests\Commands\Migrations;

use Digbang\Doctrine\Commands\Migrations\MigrateCommand;

class MigrateCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(MigrateCommand::class, new MigrateCommand());
    }
}
