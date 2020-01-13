<?php

use App\Libraries\Properties\Stats;
use App\Libraries\Units\Blademaster;
use App\Libraries\Units\Unit;

class BlademasterTest extends \ProjectTests\Support\DatabaseTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->bm = new Blademaster();
	}

	public function testGetsDatabaseStats()
	{
		$this->assertEquals(1725, $this->bm->stats->life->amount);
	}

	public function testCanAttackMinions()
	{
		$unit = new Unit('minion');

		$this->bm->attack($unit);

		$this->assertNotEmpty($this->bm->results());
	}
}
