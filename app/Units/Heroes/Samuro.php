<?php namespace App\Units\Heroes;

use App\Libraries\Action;
use App\Libraries\Outcome;
use App\Libraries\Status;
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
		/* Process all on-hit talents & abilities first */

		// Apply the armor reduction to the target
		if ($this->hasTalent('SamuroMirrorImageWayOfTheBlade') && $unit instanceof Hero)
		{
			$unit->addStatus($this->generateStatus('SamuroMirrorImageWayOfTheBlade'));
		}

		// Stack quest
		if ($this->hasTalent('SamuroWayOfIllusion') && count($this->clones) && $unit instanceof Hero)
		{
			$statusId = $this->addStatus($this->generateStatus('SamuroWayOfIllusion'));
		}

		// Hero hits increase the duration on active clones
		if ($unit instanceof Hero)
		{
			$this->reschedule('expireClones', 1);
		}

		// Calculate the damage
		$damage = $this->calculateAttackDamage($unit);
		
		// Update the crit counter
		$this->nextCrit ? $this->nextCrit-- : $this->setCrit();
		
		// Reschedule this action
		$action = new Action($this, [$this, 'A'], $unit);
		$this->actions['A'] = $this->schedule()->push($this->data->weapons[0]->period, $action);

		return $damage;
	}

	/**
	 * Cast Mirror Image.
	 */
	public function Q()
	{
		// Clear current clones and create two new ones
		$this->clones = [$this->createClone(), $this->createClone()];

		// Schedule the clones to expire
		$this->schedule('expireClones', SAMURO_CLONE_DURATION);

		// Reschedule this ability so it happens on cooldown
		$this->schedule('Q', SAMURO_COOLDOWN_Q);

		return true;
	}

	/**
	 * Cast Critical Strikes.
	 */
	public function W()
	{
		// Queue a crit on next auto
		$this->setCrit(0);
		
		// Reset attack timer to half the weapon period
		if (isset($this->actions['A']))
		{
			$stamp = $this->schedule()->timestamp() + $this->data->weapons[0]->period / 2;
			$this->schedule()->update($this->actions['A'], $stamp);
		}

		// Reschedule this ability so it happens on cooldown
		$this->schedule('W', SAMURO_COOLDOWN_W);
		
		return true;
	}

	/**
	 * Cast Wind Walk.
	 */
	public function E()
	{
		// WIP - process talent procs

		// Reschedule this ability so it happens on cooldown
		$this->schedule('E', SAMURO_COOLDOWN_E);

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
			'clone' => 0,
			'total' => 0,
		];

		// Scaled base = damage * (scaling ^ level)
		$result['base'] = $this->weapons[0]->damage * pow(1 + $this->weapons[0]->damageScale, $this->level);

		// Clones have their own base
		foreach ($this->clones as $clone)
		{
			$result['clone'] += $clone->weapons[0]->damage * pow(1 + $clone->weapons[0]->damageScale, $this->level);
		}

		// Quest damage adds a flat amount
		if ($this->hasTalent('SamuroWayOfIllusion'))
		{
			// Check if there are any stacks
			if ($statusId = $this->hasStatus('SamuroWayOfIllusion'))
			{
				$status = $this->statuses[$statusId];

				$result['quest'] = $status->stacks * $status->amount;

				// If the quest is done, add the bonus
				if ($status->stacks >= $status->maxStatus)
				{
					$result['quest'] += SAMURO_QUEST_BONUS;
				}
			}
		}

		// Is it a critical strike?
		if ($this->isCrit($unit))
		{
			$adjusted = $result['base'] + $result['quest'];

			if ($this->hasTalent('SamuroBurningBlade'))
			{
				$result['spell'] = $result['crit'] = $adjusted * 0.5;

				// Clones proc their own BB
				if ($count = count($this->clones))
				{
					$result['spell'] += $result['clone'] * 0.5 * $count;
				}
			}
			elseif ($this->hasTalent('SamuroPhantomPain'))
			{
				$result['crit'] = $adjusted * (0.5 + ($this->clones * 0.45));
			}
			else
			{
				$result['crit'] = $adjusted * 0.5;
			}

			// Clones get crits too
			if ($count = count($this->clones))
			{
				$result['clone'] += $result['clone'] * 0.5;
			}
		}

		// Calculate the total so far
		$damage = $result['base'] + $result['quest'] + $result['crit'];

		// Account for armor
		if (($statusId = $unit->hasStatus('physicalArmor')) !== null)
		{
			$status = $unit->statuses()[$statusId];
			$result['armor'] = -1 * $damage * ($status->stacks * $status->amount);
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
	 * Create a clone.
	 *
	 * @return Hero
	 */
	public function createClone(): Hero
	{
		$schedule = $this->schedule();

		$clone = new Hero('Samuro');
		$clone->setSchedule($schedule);
		
		// Clones scale the same but deal less damage
		$clone->weapons[0]->damage = $this->hasTalent('SamuroIllusionMaster') ? SAMURO_CLONE_DAMAGE * 2 : SAMURO_CLONE_DAMAGE;

		// Clones have effective 50% health without Three Blade Style
		if (! $this->hasTalent('SamuroThreeBladeStyle'))
		{
			$clone->life->amount = $this->life->amount * 0.5;
		}

		return $clone;
	}

	/**
	 * Remove any clones.
	 *
	 * @return $this
	 */
	public function expireClones(): self
	{
		$this->clones = [];

		return $this;
	}

	/**
	 * Return the number of active clones.
	 *
	 * @return int
	 */
	protected function cloneCount(): int
	{
		return count($this->clones);
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
			$num = $this->hasTalent('SamuroMirrorImageWayOfTheBlade') ? 2 : 3;
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
				
			break;
		}

		return $this;
	}

	/**
	 * Generates the appropriate Status to go along with an ability or talent.
	 *
	 * @param string $name  name of the target
	 *
	 * @return Status
	 */
	public function generateStatus(string $name): Status
	{
		switch ($name)
		{
			// Reduce physical Armor by 5 for 2.25 seconds stacking up to 3 times
			case 'SamuroMirrorImageWayOfTheBlade':
				return new Status([
					'type'      => 'physicalArmor',
					'stacks'    => 1,
					'maxStacks' => 3,
					'amount'    => -0.05,
					'duration'  => 2.25,
				]);
			break;

			// Every time a Mirror Image Critically Strikes a Hero, Samuro gains 0.25 Attack Damage, up to 10
			case 'SamuroWayOfIllusion':
				return new Status([
					'type'      => 'SamuroWayOfIllusion',
					'stacks'    => count($this->clones),
					'maxStacks' => 40,
					'amount'    => 0.25,
				]);
			break;
		}

		throw new \RuntimeException('Unknown status requested: ' . $name);
	}
}
