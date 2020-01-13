<?php namespace ProjectTests\Support\Database\Seeds;

use App\Models\StatModel;
use CodeIgniter\Database\Seeder;

class TestSeeder extends Seeder
{
	public function run()
	{
		$stats = new StatModel();

		// Seed all initial values from app
		$this->call('\App\Database\Seeds\StatsSeeder');

		// Update stats necessary for testing
		$stats->where('type', 'weapon')->where('name', 'damage')->update(null, ['value' => 102]);
		$stats->where('type', 'weapon')->where('name', 'damageScale')->update(null, ['value' => 0.04]);
		$stats->where('type', 'weapon')->where('name', 'period')->update(null, ['value' => 0.6]);
		$stats->where('type', 'life')->where('name', 'amount')->update(null, ['value' => 1725]);
		$stats->where('type', 'life')->where('name', 'regenScale')->update(null, ['value' => 3.6]);
	}
}
