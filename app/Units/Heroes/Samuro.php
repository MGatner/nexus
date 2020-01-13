<?php namespace App\Units\Heroes;

use App\Libraries\Action;
use App\Units\BaseUnit;
use App\Units\Hero;

/**
 * Class Samuro
 *
 * The Blademaster we deserve.
 */
class Samuro extends Hero
{
	/**
	 * Active clones.
	 *
	 * @var array of Heroes
	 */
	protected $clones = [];

	/**
	 * Hits until next crit.
	 *
	 * @var int
	 */
	protected $nextCrit = 4;

	/**
	 * Create the Blademaster with an intial set of values.
	 *
	 * @param array $talented  Array of talents selected
	 */
	public function __construct(int $level = 1, $talented = [])
	{
		parent::__construct('Samuro', $level, $talented);
	}

	/**
	 * Generate an attack action.
	 *
	 * @param BaseUnit $unit  Anything that can be attacked
	 */
	public function A(BaseUnit &$unit)
	{
		return new Action($this, ['attack', $unit], $this->weapons[0]->period);
	}

	/**
	 * Attack a unit returning the damage dealt of each type.
	 *
	 * @param BaseUnit $unit  Anything Samuro can attack
	 *
	 * @return array  Array of damage by type
	 */
	public function attack(BaseUnit $unit): array
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
		$result['base'] = $this->weapons[0]->damage * pow(1 + $this->weapons[0]->damageScale, $this->level);

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

		// Calculate the total so far
		$damage = $result['base'] + $result['quest'] + $result['crit'];

		// Account for armor
		if (! empty($unit->physicalArmor))
		{
			$result['armor'] = $damage * $unit->physicalArmor / 100 * -1;
		}
		
		// Harsh Winds is straight % increase
		if ($this->hasTalent('SamuroHarshWinds'))
		{
			$result['harsh'] = $damage * 0.3;
		}

		return $result;
	}

	/**
	 * Cast Mirror Image.
	 */
	public function Q()
	{
		// Reset the cooldown
		$this->cooldowns['Q'] = 14;
	}

	/**
	 * Cast Critical Strikes.
	 */
	public function W()
	{
		// Reset the cooldown
		$this->cooldowns['W'] = 10;
	}

	/**
	 * Cast Wind Walk.
	 */
	public function E()
	{
		// Reset the cooldown
		$this->cooldowns['E'] = 15;
	}

	/**
	 * Determine if an attack is a critical strike.
	 *
	 * @param BaseUnit $unit
	 *
	 * @return bool
	 */
	protected function isCrit(BaseUnit $unit): bool
	{
		// See if a crit is scheduled
		if ($this->nextCrit === 0)
		{
			return true;
		}
		
		// Otherwise it is a crit if Merciless Strikes hits a CC hero
		return $unit instanceof Hero && $unit->hasEffect(['stun', 'root', 'slow']) && $this->hasTalent('SamuroHarshWinds');
	}
}
