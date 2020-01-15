<?php

use App\Libraries\Action;
use App\Libraries\Outcome;
use App\Libraries\Schedule;
use App\Units\Hero;

class ScheduleTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->schedule = new Schedule();

		$this->raynor   = new Hero('Raynor');
		$this->raynor->setSchedule($this->schedule);

		$this->action   = new Action($this->raynor, 'time');
	}

	public function testEmptyPopReturnsNull()
	{
		$result = $this->schedule->pop();

		$this->assertNull($result);
	}

	public function testPastTimeLimitReturnsNull()
	{
		$this->schedule->timelimit = -10;
		
		$result = $this->schedule->pop();

		$this->assertNull($result);
	}

	public function testPushIncrementsId()
	{
		$id = $this->schedule->push(0, $this->action);
		$this->assertEquals(1, $id);
		
		$id = $this->schedule->push(0, $this->action);
		$this->assertEquals(2, $id);
	}

	public function testPushCreatesEntry()
	{
		$id = $this->schedule->push(0, $this->action);

		$this->assertCount(1, $this->getPrivateProperty($this->schedule, 'stamps'));
		$this->assertCount(1, $this->getPrivateProperty($this->schedule, 'actions'));
		$this->assertCount(1, $this->getPrivateProperty($this->schedule, 'ids'));
	}

	public function testPopReturnsOutcome()
	{
		$this->schedule->push(0, $this->action);

		$result = $this->schedule->pop();

		$this->assertInstanceOf(Outcome::class, $result);
	}

	public function testPopReturnsInOrder()
	{
		$action1 = new Action(new Hero('Raynor'), 'time');
		$action2 = new Action(new Hero('Samuro'), 'time');
		$action3 = new Action(new Hero('FaerieDragon'), 'time');

		$this->schedule->push(5,  $action1);
		$this->schedule->push(10, $action2);
		$this->schedule->push(1,  $action3);

		$outcome = $this->schedule->pop();
		$this->assertEquals('Brightwing', $outcome->unit->hyperlinkId);

		$outcome = $this->schedule->pop();
		$this->assertEquals('Raynor', $outcome->unit->hyperlinkId);

		$outcome = $this->schedule->pop();
		$this->assertEquals('Samuro', $outcome->unit->hyperlinkId);
	}

	public function testPopProgressesTimestamp()
	{
		$this->schedule->push(10, $this->action);

		$outcome = $this->schedule->pop();

		$this->assertEquals(10, $this->schedule->timestamp());
	}

	public function testPopReturnsRealResult()
	{
		$this->schedule->push(10, $this->action);

		$outcome  = $this->schedule->pop();

		$this->assertCloseEnough(time(), $outcome->data);
	}
}
