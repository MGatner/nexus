<?php

use App\Libraries\Status;
use App\Units\Hero;

class HeroTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->hero = new Hero('Raynor');
	}
	
	public function testDefaultDataIsNull()
	{
		$this->assertNull($this->getPrivateProperty($this->hero, 'data'));
	}
	
	public function testEnsureDataLoadsData()
	{
		isset($this->hero->foobar);

		$this->assertNotNull($this->getPrivateProperty($this->hero, 'data'));
	}
	
	public function testEnsureDataLoadsCorrectData()
	{
		$this->assertEquals('Starcraft', $this->hero->franchise);
	}
	
	public function testCanChangeData()
	{
		$this->hero->gender = 'Female';

		$this->assertEquals('Female', $this->hero->gender);
	}
	
	public function testHasOwnData()
	{
		$hero1 = new Hero('Raynor');
		$hero2 = new Hero('Raynor');
		
		$hero1->ratings->damage = 1;

		$this->assertNotEquals($hero2->ratings->damage, $hero1->ratings->damage);
	}
	
	public function testCanChangeNestedData()
	{
		$this->hero->ratings->complexity = 10;

		$this->assertEquals(10, $this->hero->ratings->complexity);
	}
	
	public function testCanResetData()
	{
		$this->hero->gender = 'Female';
		
		$this->hero->reset();

		$this->assertEquals('Male', $this->hero->gender);
	}
	
	public function testCanAddData()
	{
		$this->hero->favoriteIceCream = 'pistachio';

		$this->assertEquals('pistachio', $this->hero->favoriteIceCream);
	}

	public function testHasTalent()
	{
		$this->hero->talented = ['talent1', 'talent2', 'talent3'];

		$this->assertTrue($this->hero->hasTalent('talent2'));
		$this->assertFalse($this->hero->hasTalent('talent9'));
	}

	public function testAttackPeriodMatches()
	{
		$expected = $this->hero->weapons[0]->period;

		$this->assertEquals($expected, $this->hero->attackPeriod());
	}

	public function testAttackSpeedReducesPeriod()
	{
		$modifier = 0.20;

		$status = new Status([
			'type'      => 'attackSpeed',
			'stacks'    => 1,
			'amount'    => $modifier,
		]);
		
		$this->hero->addStatus($status);

		$this->assertLessThan($this->hero->weapons[0]->period, $this->hero->attackPeriod());
	}

	public function testAttackSpeedIncreasesPeriod()
	{
		$modifier = -0.20;

		$status = new Status([
			'type'      => 'attackSpeed',
			'stacks'    => 1,
			'amount'    => $modifier,
		]);
		
		$this->hero->addStatus($status);

		$this->assertGreaterThan($this->hero->weapons[0]->period, $this->hero->attackPeriod());
	}
}
