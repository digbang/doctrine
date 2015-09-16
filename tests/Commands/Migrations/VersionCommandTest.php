<?php namespace Tests\Commands\Migrations;

use Digbang\Doctrine\Commands\Migrations\VersionCommand;

class VersionCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(VersionCommand::class, new VersionCommand());
    }
}
