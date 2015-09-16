<?php namespace Tests\Commands\ClearCache;

use Digbang\Doctrine\Commands\ClearCache\QueryCommand;

class QueryCommandTest extends \Tests\TestCase
{
	/** @test */
	function it_should_construct()
	{
		$this->assertInstanceOf(QueryCommand::class, new QueryCommand());
	}
}
