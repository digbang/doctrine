<?php namespace Tests;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
	/**
	 * @param $className
	 *
	 * @return object|\PHPUnit_Framework_MockObject_MockObject
	 */
	protected function getEmptyMock($className)
	{
		return $this
			->getMockBuilder($className)
			->disableOriginalConstructor()
			->getMock();
	}
}
