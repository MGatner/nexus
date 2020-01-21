<?php namespace Config;

class Assets extends \Tatter\Assets\Config\Assets
{
	// Additional assets to load per route - no leading/trailing slashes
	public $routes = [
		'' => [
			'vendor/bootstrap/bootstrap.min.css',
			'vendor/bootstrap/bootstrap.bundle.min.js',
			'vendor/font-awesome/css/all.min.css',
		],
	];
}
