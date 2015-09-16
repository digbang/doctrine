<?php namespace Tests\Commands\Validate;

use Digbang\Doctrine\Commands\Validate\SchemaCommand;

class SchemaCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(SchemaCommand::class, new SchemaCommand());
    }
}
