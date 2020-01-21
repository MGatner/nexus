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
    	// Select talents
    	$talents = [
    		'SamuroMirrorImageWayOfTheBlade',
    		'SamuroMirage',
    		'SamuroCrushingBlow',
    		'SamuroHeroicAbilityBladestorm',
    		'SamuroShukuchi',
    		'SamuroPressTheAttack',
    		'SamuroWindStrider',
    	];

		// Fetch our combatants
		$samuro = new Samuro(20, $talents);
		$raynor = new Hero('raynor', 20);

		$samuro->schedule()->timelimit = 20;

		// Pre-cast abilities in the desired order
		$samuro->setCrit(0);
		$samuro->Q();
		$samuro->E();
		
		// Schedule the first attack then W immediately after
		$samuro->schedule('A', 0, $raynor);
		$samuro->schedule('W', 0.1);
				
		// Run the schedule, logging outcomes
		$rows = [];
		$total = 0;
		$count = 1;
		while ($outcome = $samuro->schedule()->pop())
		{
			if ($outcome->keep)
			{
				$row = $outcome->data;
				$row['time']  = $outcome->timestamp;
				$row['count'] = $count;

				$rows[] = array_map(function($num) { return round($num, 2); }, $row);
				
				$total += $row['total'];
				$count++;
			}
		}

		$thead = ['Base', 'Quest', 'Crush', 'Crit', 'Spell', 'Armor', 'Harsh', 'Clone', 'Total', 'Timestamp', 'ID'];
		CLI::table($rows, $thead);
		
		CLI::write('Total damage: ' . number_format($total, 2), 'green');
	}
}
