<?php namespace Tests\Commands\Migrations;

use Digbang\Doctrine\Commands\Migrations\StatusCommand;

class StatusCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(StatusCommand::class, new StatusCommand());
    }
}
