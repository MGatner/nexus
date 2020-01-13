<?php

use App\Libraries\Units\Blademaster;
use App\Libraries\Units\Unit;

class UntalentedLevel1Test extends \ProjectTests\Support\DatabaseTestCase
{
	use \ProjectTests\Support\BlademasterTrait;

	public function setUp(): void
	{
		parent::setUp();
		
		$this->bm = new Blademaster(null, []);
		$this->bm->level = 1;
	}

	public function testBasicAttackDoesCorrectDamage()
	{
		$expected = [
			'base'  => 106.08,
			'quest' => 0,
			'crit'  => 0,
			'spell' => 0,
			'armor' => 0,
			'harsh' => 0,
			'type'  => 'minion',
		];
		
		$unit = new Unit('minion');

		$this->setNextAction($this->bm, 'A');
		$this->setNextCrit($this->bm, 4);

		$this->bm->attack($unit);
		$results = $this->bm->results();

		$this->assertEquals($expected, $results[0]);
	}

	public function testCritAttackDoesCorrectDamage()
	{
		$expected = [
			'base'  => 106.08,
			'quest' => 0,
			'crit'  => 53.04,
			'spell' => 0,
			'armor' => 0,
			'harsh' => 0,
			'type'  => 'minion',
		];
		
		$unit = new Unit('minion');

		$this->setNextAction($this->bm, 'A');
		$this->setNextCrit($this->bm, 0);

		$this->bm->attack($unit);
		$results = $this->bm->results();

		$this->assertEquals($expected, $results[0]);
	}

	public function testBasicAttackResetsCooldown()
	{
		$unit = new Unit('minion');

		$this->setNextAction($this->bm, 'A');

		$this->bm->attack($unit);
		$elapsed = $this->bm->attack($unit);

		$results   = $this->bm->results();
		$cooldowns = $this->getPrivateProperty($this->bm, 'cooldowns');

		$this->assertCount(2, $results);
		$this->assertEquals(0.6, $elapsed);
		$this->assertEquals(0.6, $cooldowns['A']);
	}
}
