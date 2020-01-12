<?php namespace App\Database\Seeds;

use App\Models\StatModel;

class StatsSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		// Define necessary hero stats
		$types = [
			'weapon'  => ['range', 'period', 'damage', 'damageScale'],
			'life'    => ['amount', 'scale', 'regenRate', 'regenScale'],
			'hero'    => ['innerRadius', 'radius', 'sight', 'speed'],
			'ratings' => ['complexity', 'damage', 'survivability', 'utility'],			
		];
		
		// Check for and create missing stats
		$stats = new StatModel();
		foreach ($types as $type => $names)
		{
			foreach ($names as $name)
			{
				if (! $stats->where('type', $type)->where('name', $name)->first())
				{
					$stats->insert(['name' => $name, 'type' => $type, 'value' => 0]);
				}
			}
		}
	}
}
