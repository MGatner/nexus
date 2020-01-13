<?php

use App\Units\Hero;

class HeroTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function setUp(): void
	{
		parent::setUp();
		
		$this->hero = new Hero('Raynor');
	}
	
	public function testDefaultDataIsNull()
	{
		$this->assertNull($this->getPrivateProperty($this->hero, 'default'));
	}
	
	public function testEnsureDataLoadsData()
	{
		isset($this->hero->foobar);

		$this->assertNotNull($this->getPrivateProperty($this->hero, 'default'));
	}
	
	public function testEnsureDataLoadsCorrectData()
	{
		$this->assertEquals('Starcraft', $this->hero->franchise);
	}
	
	public function testCanChangeData()
	{
		$this->hero->gender = 'Female';

		$this->assertEquals('Female', $this->hero->gender);
	}
	
	public function testCanChangeNestedData()
	{
		$this->hero->ratings->complexity = 10;

		$this->assertEquals(10, $this->hero->ratings->complexity);
	}
	
	public function testCanResetData()
	{
		$this->hero->gender = 'Female';
		
		$this->hero->reset();

		$this->assertEquals('Male', $this->hero->gender);
	}
	
	public function testCanAddData()
	{
		$this->hero->favoriteIceCream = 'pistachio';

		$this->assertEquals('pistachio', $this->hero->favoriteIceCream);
	}

	public function testHasTalent()
	{
		$this->hero->talented = ['talent1', 'talent2', 'talent3'];

		$this->assertTrue($this->hero->hasTalent('talent2'));
		$this->assertFalse($this->hero->hasTalent('talent9'));
	}
}
