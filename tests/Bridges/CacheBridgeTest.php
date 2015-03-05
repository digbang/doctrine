<?php namespace Tests\Cache;

use Digbang\Doctrine\Bridges\CacheBridge;
use Illuminate\Cache\Repository;
use Illuminate\Cache\StoreInterface;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;

class CacheBridgeTest extends PHPUnit_Framework_TestCase
{
	const AN_EXISTING_KEY    = 'an_existing_key';
	const A_NON_EXISTING_KEY = 'a_non_existing_key';
	const AN_EXISTING_VALUE  = 'An existing value';

	/**
	 * @type CacheBridge
	 */
	private $cb;

	/**
	 * @type PHPUnit_Framework_MockObject_MockObject
	 */
	private $laravelCache;

	/**
	 * @type PHPUnit_Framework_MockObject_MockObject
	 */
	private $store;

	protected function setUp()
	{
		$this->laravelCache = $this
			->getMockBuilder(Repository::class)
			->disableOriginalConstructor()
			->getMock();

		$this->store = $this
			->getMockBuilder(StoreInterface::class)
			->disableOriginalConstructor()
			->getMock();

		$this->cb = new CacheBridge($this->laravelCache);

		$version = rand(0, 10);

		$this->laravelCache
			->expects($this->any())
			->method('get')
			->will(
				$this->returnValueMap([
					['DoctrineNamespaceCacheKey[]',              null, $version],
					["[".self::AN_EXISTING_KEY."][$version]",    null, self::AN_EXISTING_VALUE],
					["[".self::A_NON_EXISTING_KEY."][$version]", null, false]
				])
			);

		$this->laravelCache
			->expects($this->any())
			->method('has')
			->will($this->returnValueMap([
				["[".self::AN_EXISTING_KEY."][$version]",    true],
				["[".self::A_NON_EXISTING_KEY."][$version]", false]
			]));
	}

	/** @test */
	public function it_should_call_on_laravels_cache_on_contains()
	{
		$this->assertTrue($this->cb->contains(self::AN_EXISTING_KEY), "Existing key not found.");
		$this->assertFalse($this->cb->contains(self::A_NON_EXISTING_KEY), "Non-existing key found.");
	}

	/** @test */
	public function it_should_call_on_laravels_cache_on_fetch()
	{
		$this->assertEquals(self::AN_EXISTING_VALUE, $this->cb->fetch(self::AN_EXISTING_KEY), "Existing value not fetched.");
		$this->assertFalse($this->cb->fetch(self::A_NON_EXISTING_KEY), "Non Existing value is not false.");
	}

	/** @test */
	public function it_should_call_on_laravels_cache_on_save()
	{
		$this->laravelCache
			->expects($this->atLeastOnce())
			->method('put');

		$this->assertTrue($this->cb->save('some', 'Value'), "Save not working?");
	}

	/** @test */
	public function it_should_call_on_laravels_cache_on_delete()
	{
		$this->laravelCache
			->expects($this->atLeastOnce())
			->method('getStore')
			->willReturn($this->store);

		$this->store
			->expects($this->once())
			->method('forget');

		$this->assertTrue($this->cb->delete('a_key'), "Delete not working?");
	}

	/** @test */
	public function it_should_call_on_laravels_cache_on_flush_all()
	{
		$this->laravelCache
			->expects($this->atLeastOnce())
			->method('getStore')
			->willReturn($this->store);

		$this->store
			->expects($this->once())
			->method('flush');

		$this->assertTrue($this->cb->flushAll(), "Flush not working?");
	}
}
