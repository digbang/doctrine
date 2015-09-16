<?php namespace Tests\Commands\ClearCache;

use Digbang\Doctrine\Commands\ClearCache\MetadataCommand;

class MetadataCommandTest extends \Tests\TestCase
{
	/** @test */
	function it_should_construct()
	{
		$this->assertInstanceOf(MetadataCommand::class, new MetadataCommand());
	}
}
