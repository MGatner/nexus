<?php

use App\Libraries\Action;
use App\Libraries\Schedule;
use App\Units\Hero;
use App\Units\Heroes\Samuro;

class BasicSimulationTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->schedule = new Schedule();

		$this->samuro = new Samuro();
		$this->samuro->setSchedule($this->schedule);

		$this->raynor = new Hero('Raynor');
		$this->raynor->setSchedule($this->schedule);

		// Since stats change with patches we'll use preset numbers for testing
		$this->samuro->weapons[0]->period = 0.6;
		$this->samuro->weapons[0]->damage = 102;
		$this->samuro->weapons[0]->damageScale = 0.04;
	}

	public function testKeepsAttackingUntilTimeLimit()
	{
		$this->schedule->timelimit = 30;

		$this->samuro->schedule('A', 0, $this->raynor);

		while ($this->schedule->pop())
		{
		}

		$this->assertEquals(30, (int) $this->schedule->timestamp());
	}

	public function testExpectedNumberOfAttacks()
	{
		$this->schedule->timelimit = 30;

		$this->samuro->schedule('A', 0, $this->raynor);

		$count = 0;
		while ($this->schedule->pop())
		{
			$count++;
		}

		$this->assertEquals(52, $count);
	}

	public function testExpectedDamage()
	{
		$this->schedule->timelimit = 30;

		$this->samuro->schedule('A', 0, $this->raynor);

		$damage = 0;
		while ($outcome = $this->schedule->pop())
		{
			$damage += $outcome->data['total'];
		}

		$this->assertEquals(6205, (int) $damage);
	}

	public function testBurningBlade()
	{
		$this->schedule->timelimit = 30;

		$this->samuro->level = 20;
		$this->samuro->selectTalent('SamuroBurningBlade');
		
		$this->samuro->schedule('A', 0, $this->raynor);

		$damage = 0;
		while ($outcome = $this->schedule->pop())
		{
			$damage += $outcome->data['spell'];
		}

		$this->assertEquals(1452, (int) $damage);
	}
}
