<?php

use App\Libraries\Outcome;
use App\Units\Hero;
use App\Units\Heroes\Samuro;

class SamuroTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->samuro = new Samuro();
		$this->raynor = new Hero('Raynor');

		// Since stats change with patches we'll use a preset number
		$this->samuro->weapons[0]->damage = 102;
	}

	public function testLoadData()
	{
		$this->assertEquals('Samu', $this->samuro->attributeId);
	}

	public function testAbilitiesReturnRawResults()
	{
		$result = $this->samuro->A($this->raynor);
		$this->assertIsArray($result);

		$result = $this->samuro->E();
		$this->assertTrue($result);
	}

	public function testCanAttack()
	{
		$result = $this->samuro->calculateAttackDamage($this->raynor);

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
			'total' => 106.08,
		];

		$result = $this->samuro->calculateAttackDamage($this->raynor);

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
			'total' => 159.12,
		];
		$this->setPrivateProperty($this->samuro, 'nextCrit', 0);

		$result = $this->samuro->calculateAttackDamage($this->raynor);

		$this->assertEquals($expected, $result);
	}
}
