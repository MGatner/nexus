<?php

use App\Libraries\Schedule;
use App\Libraries\Status;
use App\Units\Hero;

class StatusTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->schedule = new Schedule();

		$this->raynor   = new Hero('Raynor');
		$this->raynor->setSchedule($this->schedule);
		
		$this->status = new Status([
			'type'      => 'slow',
			'unique'    => true,
			'stacks'    => null,
			'maxStacks' => null,
			'amount'    => 0.25,
			'duration'  => 4,
		]);
	}

	public function testUnspecifiedIsUnique()
	{
		$status = new Status();
		
		$this->assertTrue($status->unique);
	}

	public function testAddStatusAdds()
	{
		$this->raynor->addStatus(clone $this->status);
		
		$statuses = $this->raynor->statuses();

		$this->assertCount(1, $statuses);
		$this->assertEquals($this->status->type, $statuses[0]->type);
	}

	public function testStackingStacks()
	{
		$this->status->stacks = 1;

		$this->raynor->addStatus(clone $this->status);
		$this->raynor->addStatus(clone $this->status);
		$this->raynor->addStatus(clone $this->status);

		$statuses = $this->raynor->statuses();
		$this->assertCount(1, $statuses);
	
		$status = reset($statuses);
		$this->assertEquals(3, $status->stacks);
	}

	public function testStacksRespectMax()
	{
		$this->status->stacks    = 1;
		$this->status->maxStacks = 2;

		$this->raynor->addStatus(clone $this->status);
		$this->raynor->addStatus(clone $this->status);
		$this->raynor->addStatus(clone $this->status);

		$statuses = $this->raynor->statuses();
		$this->assertCount(1, $statuses);
	
		$status = reset($statuses);
		$this->assertEquals(2, $status->stacks);
	}

	public function testNonStackingReplaces()
	{
		$this->raynor->addStatus(clone $this->status);

		$this->status->amount = 0.4;

		$this->raynor->addStatus(clone $this->status);
		
		$statuses = $this->raynor->statuses();
		$this->assertCount(1, $statuses);

		$status = reset($statuses);
		$this->assertEquals(0.4, $status->amount);
	}

	public function testNonUniqueDuplicates()
	{
		$this->status->unique = false;

		$this->raynor->addStatus(clone $this->status);
		$this->raynor->addStatus(clone $this->status);
		$this->raynor->addStatus(clone $this->status);

		$statuses = $this->raynor->statuses();
		$this->assertCount(3, $statuses);
	}

	public function testRemoveStatusRemoves()
	{
		$statusId = $this->raynor->addStatus(clone $this->status);

		$this->raynor->removeStatus($statusId);

		$this->assertCount(0, $this->raynor->statuses());
	}

	public function testExpiresAfterDuration()
	{
		$this->raynor->addStatus(clone $this->status);

		while ($this->schedule->pop()) { }

		$this->assertCount(0, $this->raynor->statuses());
		$this->assertEquals($this->schedule->timestamp(), $this->status->duration);
	}
}
