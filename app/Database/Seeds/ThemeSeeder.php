<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use Tatter\Settings\Models\SettingModel;
use Tatter\Themes\Models\ThemeModel;

class ThemeSeeder extends Seeder
{
	public function run()
	{
		// Check for the theme setting
		$settings = new SettingModel();
		
		if (! $settings->where('name', 'theme')->first())
		{
			// No setting - add the template
			$row = [
				'name'       => 'theme',
				'scope'      => 'user',
				'content'    => '1',
				'protected'  => 0,
				'summary'    => 'Site display theme',
			];

			$settings->save($row);			
		}

		// Define the initial themes
		$rows = [
			[
				'name'         => 'Default',
				'path'         => 'themes/default',
				'description'  => 'Default theme',
			],
			[
				'name'         => 'Dark',
				'path'         => 'themes/dark',
				'description'  => 'Midnight',
				'dark'         => 1,
			],
		];				
				
		// Check for and create each theme
		$themes = new ThemeModel();
		foreach ($rows as $row)
		{
			$theme = $settings->where('name', $row['name'])->first();
			
			if (! $themes->where('name', $row['name'])->first())
			{
				// No match, add it
				$themes->insert($row);
			}
		}
	}
}
