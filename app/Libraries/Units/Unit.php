<?php namespace App\Libraries\Units;

use App\Libraries\Properties\Stats;

/**
 * Class Unit
 *
 * Something Blademaster can attack.
 */
class Unit
{
	/**
	 * Type of the unit.
	 *
	 * @var string  E.g. 'hero', 'mercenary', 'minion', 'structure', 'summon'
	 */
	public $type;

	/**
	 * Default stats for this unit.
	 *
	 * @var object
	 */
	public $stats;

	/**
	 * Current health.
	 *
	 * @var int
	 */
	public $health;

	/**
	 * Create the target with an intial set of values.
	 *
	 * @param string $type  The type of unit
	 * @param Stats $stats  Unit default stats
	 */
	public function __construct($type, Stats $stats = null)
	{
		$this->type = $type;

		// If no stats were provided then set required properties
		if ($stats === null)
		{
			$stats = new Stats();
			$stats->life->amount = 10000;
		}
		$this->stats = $stats;
		
		// Set the initial health
		$this->health = $stats->life->amount;
	}
}
