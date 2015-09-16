<?php namespace Tests\Commands\Migrations;

use Digbang\Doctrine\Commands\Migrations\GenerateCommand;

class GenerateCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(GenerateCommand::class, new GenerateCommand());
    }
}