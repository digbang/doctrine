<?php namespace Tests\Commands\ClearCache;

use Digbang\Doctrine\Commands\ClearCache\ResultCommand;

class ResultCommandTest extends \Tests\TestCase
{
	/** @test */
	function it_should_construct()
	{
		$this->assertInstanceOf(ResultCommand::class, new ResultCommand());
	}
}
