<?php

use App\Units\Hero;
use App\Units\Heroes\Samuro;

class SamuroTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->samuro = new Samuro();
		$this->raynor = new Hero('Raynor');
	}

	public function testLoadData()
	{
		$this->assertEquals('Samu', $this->samuro->attributeId);
	}

	public function testCanAttack()
	{
		$result = $this->samuro->attack($this->raynor);

		$this->assertNotEmpty($result);
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
		];

		$result = $this->samuro->attack($this->raynor);

		$this->assertEquals($expected, $result);
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
		];
		$this->setPrivateProperty($this->samuro, 'nextCrit', 0);

		$result = $this->samuro->attack($this->raynor);

		$this->assertEquals($expected, $result);
	}
}
