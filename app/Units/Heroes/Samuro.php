<?php namespace App\Units\Heroes;

use App\Libraries\Action;
use App\Libraries\Outcome;
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
	protected $nextCrit;

	/**
	 * Stacks towards Way of Illusion.
	 *
	 * @var int|null
	 */
	protected $illusionStacks;

	/**
	 * Create the Blademaster with an intial set of values.
	 *
	 * @param int $level          Current level for this hero
	 * @param array $talented     Array of talents selected
	 */
	public function __construct(int $level = 1, $talented = [])
	{
		parent::__construct('Samuro', $level, $talented);

		$this->setCrit();
	}

	/**
	 * Generate an attack action.
	 *
	 * @param BaseUnit $unit  Anything that can be attacked
	 */
	public function A(BaseUnit $unit)
	{
		$damage = $this->calculateAttackDamage($unit);

		$this->nextCrit ? $this->nextCrit-- : $this->setCrit();

		// Reduce physical Armor by 5 for 2.25 seconds stacking up to 3 times
		if ($this->hasTalent('SamuroMirrorImageWayOfTheBlade'))
		{
			
		}

		// WIP - process all on-hit talents & abilities

		// Reschedule this action
		$action = new Action($this, [$this, 'A'], $unit);
		$this->schedule()->push($this->current->weapons[0]->period, $action);

		return $damage;
	}

	/**
	 * Cast Mirror Image.
	 */
	public function Q()
	{

		// WIP - spawn clones

		$action = new Action($this, [$this, 'Q']);
		$this->schedule()->push(SAMURO_COOLDOWN_Q, $action);

		return true;
	}

	/**
	 * Cast Critical Strikes.
	 */
	public function W()
	{
		// WIP - set nextCrit and an expiry timer, reset AA
		
		$action = new Action($this, [$this, 'W']);
		$this->schedule()->push(SAMURO_COOLDOWN_W, $action);
		
		return true;
	}

	/**
	 * Cast Wind Walk.
	 */
	public function E()
	{
		// WIP - process talent procs

		$action = new Action($this, [$this, 'E']);
		$this->schedule()->push(SAMURO_COOLDOWN_E, $action);
		
		return true;
	}

	/**
	 * Calculate the damage dealt broken into its parts.
	 *
	 * @param BaseUnit $unit  Anything Samuro can attack
	 *
	 * @return array  Array of damage by type
	 */
	public function calculateAttackDamage(BaseUnit $unit): array
	{
		// Tally each damage source as we go
		$result = [
			'base'  => 0,
			'quest' => 0,
			'crit'  => 0,
			'spell' => 0,
			'armor' => 0,
			'harsh' => 0,
			'total' => 0,
		];

		// Scaled base = damage * (scaling ^ level)
		$result['base'] = $this->weapons[0]->damage * pow(1 + $this->weapons[0]->damageScale, $this->level);

		// Quest damage adds a flat amount
		if ($this->hasTalent('SamuroWayOfIllusion'))
		{
			$result['quest'] = min(10, 0.25 * $this->illusionStacks);
			if ($this->illusionStacks >= 40)
			{
				$result['quest'] += 20;
			}
		}

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

		// Tally it up
		$result['total'] = array_sum($result);

		return $result;
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
		return $unit instanceof Hero && $unit->hasStatus(['stun', 'root', 'slow']) && $this->hasTalent('SamuroHarshWinds');
	}

	/**
	 * Set the number of attacks until next crit.
	 *
	 * @param int|null $num
	 *
	 * @return $this
	 */
	public function setCrit(int $num = null): self
	{
		// If no number was passed then set it based off talents
		if ($num === null)
		{
			$num = $this->hasTalent('SamuroMirrorImageWayOfTheBlade') ? 3 : 4;
		}
		$this->nextCrit = $num;

		return $this;
	}

	/**
	 * Add a talent to the list of selected talents, processing any specific effects.
	 *
	 * @param string $nameId  nameId of the target talent
	 *
	 * @return $this
	 */
	public function selectTalent(string $nameId)
	{
		parent::selectTalent($nameId);
		
		switch ($nameId)
		{
			case 'SamuroWayOfIllusion':
				// WIP - need to implement stacking
				$this->illusionStacks = 40;
			break;
		}

		return $this;
	}
}
