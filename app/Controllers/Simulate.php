<?php namespace App\Controllers;

use App\Units\Hero;
use App\Units\Heroes\Samuro;

class Simulate extends BaseController
{
	public function index()
	{
		// Fetch our combatants
		$samuro = new Samuro();
		$raynor = new Hero('raynor');

		// Prep abilities
		$samuro->W();
		$samuro->Q();
		$samuro->E();
		
		// Attack!
		$samuro->A($raynor);

		// Run the schedule, logging outcomes
		$rows = [];
		while ($outcome = $this->schedule->pop())
		{
			$row = $outcome->data;
			$row['time'] = $outcome->timestamp;

			$rows[] = $row;
		}

		$thead = ['Task ID', 'Name', 'Category', 'UID', 'Class', 'Summary'];
		CLI::table($rows, $thead);
	}
}
