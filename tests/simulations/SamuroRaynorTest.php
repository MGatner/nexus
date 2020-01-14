<?php

use App\Libraries\Action;
use App\Units\Hero;
use App\Units\Heroes\Samuro;

class SamuroRaynorTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->samuro = new Samuro();
		$this->raynor = new Hero('Raynor');
	}

	public function testTest()
	{
		$this->assertTrue(true);
	}
}
