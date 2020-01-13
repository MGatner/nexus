<?php

use App\Models\StatModel;

class SeederTest extends \ProjectTests\Support\DatabaseTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->model = new StatModel();
	}

	public function testDatabaseGetSeeded()
	{
		$stat = $this->model->first();

		$this->assertIsInt($stat->id);
	}

	public function testDefaultValuesZero()
	{
		$stat = $this->model->where('name', 'survivability')->first();

		$this->assertEquals(0, $stat->value);
	}

	public function testOverridesAreSet()
	{
		$stat = $this->model->where('name', 'regenScale')->first();

		$this->assertEquals(3.6, $stat->value);
	}
}
