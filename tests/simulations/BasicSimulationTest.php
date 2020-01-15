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

		$this->samuro->A($this->raynor);

		while ($this->schedule->pop()) {
		}

		$this->assertEquals(30, $this->schedule->timestamp());
	}

	public function testExpectedNumberOfAttacks()
	{
		$this->schedule->timelimit = 30;

		$this->samuro->A($this->raynor);
		$count = 1;

		while ($this->schedule->pop())
		{
			$count++;
		}

		$this->assertEquals(51, $count);
	}

	public function testExpectedDamage()
	{
		$this->schedule->timelimit = 30;

		$data   = $this->samuro->A($this->raynor);
		$damage = $data['total'];

		while ($outcome = $this->schedule->pop())
		{
			$damage += $outcome->data['total'];
		}

		$this->assertEquals(5940, (int) $damage);
	}
}
