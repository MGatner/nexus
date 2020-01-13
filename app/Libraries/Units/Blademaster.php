<?php namespace App\Libraries\Units;

use App\Libraries\Properties\Stats;
use App\Models\AbilityModel;

/**
 * Class Blademaster
 *
 * The one we deserve.
 */
class Blademaster extends Hero
{
	/**
	 * Number of active clones.
	 *
	 * @var int
	 */
	public $clones = 2;

	/**
	 * Hits until next crit.
	 *
	 * @var int
	 */
	protected $nextCrit;

	/**
	 * Results of every basic attack.
	 *
	 * @var array
	 */
	protected $results = [];

	/**
	 * Create the Blademaster with an intial set of values.
	 *
	 * @param Stats $stats     Stats to use for Blademaster
	 * @param array $talented  Array of talents selected
	 */
	public function __construct(Stats $stats = null, $talented = [])
	{
		// If no stats were provided then load them from the database
		if ($stats === null)
		{
			$stats = new Stats();
			$stats->loadFromDatabase();
		}		

		parent::__construct($stats);
		
		$this->talented = $talented;
	}

	/**
	 * Return all results.
	 *
	 * @return array
	 */
	public function results(): array
	{
		return $this->results;
	}

	/**
	 * Attack the target unit using the next available action.
	 *
	 * @param Unit $unit  Anything Blademaster can attack
	 *
	 * @return float  Seconds elapsed since last attack
	 */
	public function attack(Unit &$unit): float
	{
		// Check cooldowns for the next available action
		$action = min(array_keys($this->cooldowns, min($this->cooldowns)));

		// Save the time that elapses before reaching this action
		$elapsed = $this->cooldowns[$action];

		// Run the action
		$this->$action($unit);
		
		// Return the time
		return $elapsed;
	}

	/**
	 * Attack the target unit and store the result.
	 *
	 * @param Unit $unit  Anything Blademaster can attack
	 */
	public function A(Unit &$unit)
	{
		// Determine if this will be a critical strike
		$crit = $this->isCrit($unit);

		// Calculate the damage dealt
		$result = $this->calculateDamage($unit);

		// Add some metadata
		$result['type'] = $unit->type;
		
		// Store the result
		$this->results[] = $result;

		// Set the cooldown for the next attack
		$this->cooldowns['A'] = $this->stats->weapon->period;
	}

	/**
	 * Cast Mirror Image.
	 *
	 * @param Unit $unit  Anything Blademaster can attack
	 */
	public function Q(Unit &$unit)
	{
		// Reset the cooldown
		$this->cooldowns['Q'] = 14;
	}

	/**
	 * Cast Critical Strikes.
	 *
	 * @param Unit $unit  Anything Blademaster can attack
	 */
	public function W(Unit &$unit)
	{
		// Reset the cooldown
		$this->cooldowns['W'] = 10;
	}

	/**
	 * Cast Wind Walk.
	 *
	 * @param Unit $unit  Anything Blademaster can attack
	 */
	public function E(Unit &$unit)
	{
		// Reset the cooldown
		$this->cooldowns['E'] = 15;
	}

	/**
	 * Calculate the damage dealt to a target.
	 *
	 * @param Unit $unit  Anything Blademaster can attack
	 *
	 * @return array  Array of damage by type
	 */
	protected function calculateDamage(Unit $unit): array
	{
		// Tally each damage source as we go
		$result = [
			'base'  => 0,
			'quest' => 0,
			'crit'  => 0,
			'spell' => 0,
			'armor' => 0,
			'harsh' => 0,
		];

		// Scaled base = damage * (scaling ^ level)
		$result['base'] = $this->stats->weapon->damage * pow(1 + $this->stats->weapon->damageScale, $this->level);

		// Quest damage adds a flat amount
		$result['quest'] = $this->hasTalent('SamuroWayOfIllusion') ? 30 : 0;

		// Is it a critical strike?
		if ($this->isCrit($unit))
		{
			$adjusted = $result['base'] + $result['quest'];

			if ($this->hasTalent('SamuroBurningBlade'))
			{
				$result['spell'] = $result['crit'] = $adjusted * 0.5;
			}
			elseif ($this->hasTalent('SamuroPhantomPain'))
			{
				$result['crit'] = $adjusted * (0.5 + ($this->clones * 0.45));
			}
			else
			{
				$result['crit'] = $adjusted * 0.5;
			}			
		}
		
		// Account for armor
		if (! empty($unit->physicalArmor))
		{
			$dmg = $result['base'] + $result['quest'] + $result['crit'];
			$result['armor'] = $dmg * $unit->physicalArmor / 100 * -1;
		}

		return $result;
	}

	/**
	 * Determine if an attack is a critical strike.
	 *
	 * @param Unit $unit
	 *
	 * @return bool
	 */
	protected function isCrit(Unit $unit): bool
	{
		// See if a crit is scheduled
		if ($this->nextCrit === 0)
		{
			return true;
		}
		
		// Otherwise it is a crit if Merciless Strikes hits a CC hero
		return $unit->type == 'hero' && $unit->hasEffect(['stun', 'root', 'slow']) && $this->hasTalent('SamuroHarshWinds');
	}
}
