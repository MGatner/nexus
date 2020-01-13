<?php

use App\Libraries\Properties\Stats;
use App\Libraries\Units\Hero;

class HeroTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->hero = new Hero();
	}

	public function testHasStats()
	{
		$this->assertInstanceOf(Stats::class, $this->hero->stats);
	}

	public function testGetsDefaultStats()
	{
		$this->assertEquals(10000, $this->hero->stats->life->amount);
	}

	public function testHasTalent()
	{
		$this->hero->talented = ['talent1', 'talent2', 'talent3'];
		
		$this->assertTrue($this->hero->hasTalent('talent2');
		$this->assertFalse($this->hero->hasTalent('talent9');
	}
}
