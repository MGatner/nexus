<?php

use App\Libraries\Action;
use App\Libraries\Nexus;
use App\Libraries\Queue;
use App\Units\Hero;
use App\Units\Heroes\Samuro;

class SamuroRaynorTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->queue  = new Queue();
		$this->nexus  = new Nexus($this->queue);
		$this->samuro = new Samuro();
		$this->raynor = new Hero('Raynor');
	}

	public function testRepeatActions()
	{
		$action = $this->samuro->A($this->raynor);

		$this->assertInstanceOf(Action::class, $action);
	}
}
