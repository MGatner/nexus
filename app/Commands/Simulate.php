<?php namespace App\Commands;

use App\Units\Hero;
use App\Units\Heroes\Samuro;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class Simulate extends BaseCommand
{
    protected $group       = 'Nexus';
    protected $name        = 'simulate';
    protected $description = 'Run the simulation';
    
	protected $usage     = 'simulate';
	protected $arguments = [];

	public function run(array $params = [])
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
		while ($outcome = $samuro->schedule()->pop())
		{
			if ($outcome->keep)
			{
				$row = $outcome->data;
				$row['time'] = $outcome->timestamp;

				$rows[] = $row;
			}
		}

		$thead = ['Base', 'Quest', 'Crit', 'Spell', 'Armor', 'Harsh', 'Total', 'Timestamp'];
		CLI::table($rows, $thead);
	}
}
