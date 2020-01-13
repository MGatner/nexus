<?php namespace App\Libraries\Properties;

use App\Models\StatModel;

/**
 * Class Stats
 *
 * Blademaster hero statistics.
 */
class Stats
{
	/**
	 * Hero stats.
	 *
	 * @var object
	 */
	public $hero;

	/**
	 * Life stats.
	 *
	 * @var object
	 */
	public $life;

	/**
	 * Weapon stats.
	 *
	 * @var object
	 */
	public $weapon;

	/**
	 * Ratings stats.
	 *
	 * @var object
	 */
	public $ratings;

	/**
	 * Create the empty objects.
	 *
	 * @param array  $data
	 */
	public function __construct()
	{
		foreach (['hero', 'life', 'weapon', 'ratings'] as $type)
		{
			$this->{$type} = new \stdClass();
		}
	}

	/**
	 * Load Stats from the database.
	 *
	 * @param array  $data
	 */
	public function loadFromDatabase()
	{
		foreach ((new StatModel())->findAll() as $stat)
		{
			$this->{$stat->type}->{$stat->name} = $stat->value;
		}
	}
}
