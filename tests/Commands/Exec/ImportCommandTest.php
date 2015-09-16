<?php namespace Tests\Commands\Exec;

use Digbang\Doctrine\Commands\Exec\ImportCommand;

class ImportCommandTest extends \Tests\TestCase
{
	/** @test */
	function it_should_construct()
	{
		$this->assertInstanceOf(ImportCommand::class, new ImportCommand());
	}
}
