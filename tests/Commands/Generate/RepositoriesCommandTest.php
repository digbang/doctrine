<?php namespace Tests\Commands\Generate;

use Digbang\Doctrine\Commands\Generate\RepositoriesCommand;

class RepositoriesCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(RepositoriesCommand::class, new RepositoriesCommand());
    }
}
