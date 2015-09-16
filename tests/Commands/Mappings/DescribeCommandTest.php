<?php namespace Tests\Commands\Mappings;

use Digbang\Doctrine\Commands\Mappings\DescribeCommand;

final class DescribeCommandTest extends \Tests\TestCase
{
    /** @test */
    function it_should_construct()
    {
        $this->assertInstanceOf(DescribeCommand::class, new DescribeCommand());
    }
}
