<?php namespace App\Controllers;

class Home extends BaseController
{
	public function index()
	{
		return redirect()->to(site_url('simulate/samuro'));
	}
	
	// Toggle the current theme between
	// Alternates between Default (1, light) and Midnight (2, dark)
	public function theme()
	{
		// Get the current theme
		$settings = service('settings');
		$themeId  = $settings->theme;
		
		// Verify the new theme
		$themes = new ThemeModel();
		if ($theme = $themes->find(3 - $themeId))
		{
			$settings->theme = $theme->id;
		}
		
		// Send back
		return redirect()->back();
	}
}
