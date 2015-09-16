<?php namespace Tests\Commands\Validate;

use Digbang\Doctrine\Commands\Validate\ProductionCommand;

class ProductionCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(ProductionCommand::class, new ProductionCommand());
    }
}
