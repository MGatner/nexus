<?php

use App\Libraries\Outcome;
use App\Libraries\Status;
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
			'clone' => 0,
			'total' => 106.08,
		];

		$result = $this->samuro->calculateAttackDamage($this->raynor);

		$this->assertEquals($expected, $result);
	}

	public function testCritAttackDoesCorrectDamage()
	{
		$this->samuro->setCrit(0);

		$expected = [
			'base'  => 106.08,
			'quest' => 0,
			'crit'  => 53.04,
			'spell' => 0,
			'armor' => 0,
			'harsh' => 0,
			'clone' => 0,
			'total' => 159.12,
		];

		$result = $this->samuro->calculateAttackDamage($this->raynor);

		$this->assertEquals($expected, $result);
	}

	public function testArmorReducesDamage()
	{
		$status = new Status([
			'type'      => 'physicalArmor',
			'stacks'    => 1,
			'amount'    => 0.10,
		]);
		$this->raynor->addStatus($status);

		$expected = [
			'base'  => 106.08,
			'quest' => 0,
			'crit'  => 0,
			'spell' => 0,
			'armor' => -10.608,
			'harsh' => 0,
			'clone' => 0,
			'total' => 95.472,
		];

		$result = $this->samuro->calculateAttackDamage($this->raynor);

		$this->assertEquals($expected, $result);
	}

	public function testNegativeArmorIncreaseDamage()
	{
		$status = new Status([
			'type'      => 'physicalArmor',
			'stacks'    => 1,
			'amount'    => -0.10,
		]);
		$this->raynor->addStatus($status);

		$expected = [
			'base'  => 106.08,
			'quest' => 0,
			'crit'  => 0,
			'spell' => 0,
			'armor' => 10.608,
			'harsh' => 0,
			'clone' => 0,
			'total' => 116.688,
		];

		$result = $this->samuro->calculateAttackDamage($this->raynor);

		$this->assertEquals($expected, $result);
	}

	public function testBladeReducesArmor()
	{
		$this->samuro->selectTalent('SamuroMirrorImageWayOfTheBlade');

		$result = $this->samuro->A($this->raynor);

		$statuses = $this->raynor->statuses();
		
		$this->assertEquals('physicalArmor', $statuses[0]->type);
		$this->assertLessThan(0, $statuses[0]->amount);
	}

	public function testCreateCloneCreatesHero()
	{
		$clone = $this->samuro->createClone();
		
		$this->assertInstanceOf(Hero::class, $clone);
	}

	public function testCloneHasCorrectStats()
	{
		$clone = $this->samuro->createClone();

		$this->assertEquals(SAMURO_CLONE_DAMAGE, $clone->weapons[0]->damage);
		$this->assertEquals($this->samuro->life->amount * 0.5, $clone->life->amount);
	}

	public function testClonesDealDamage()
	{
		$this->samuro->Q();

		$result = $this->samuro->calculateAttackDamage($this->raynor);

		$this->assertEquals(22.88, round($result['clone'], 2));
	}
}
