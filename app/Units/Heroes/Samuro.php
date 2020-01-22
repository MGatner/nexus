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

		// If Windwalk is active then cancel it; apply Harsh Winds if talented
		$statusId = $this->hasStatus('SamuroWindwalk');
		if ($statusId !== null)
		{
			$this->removeStatus($statusId);

			if ($this->hasTalent('SamuroHarshWinds'))
			{
				$unit->addStatus($this->generateStatus('SamuroHarshWinds'));
			}
		}

		// Hero hits increase the duration on active clones
		if ($unit instanceof Hero)
		{
			$this->reschedule('expireClones', 1);
		}

		// WotB: Apply the armor reduction to the target
		if ($this->hasTalent('SamuroMirrorImageWayOfTheBlade') && $unit instanceof Hero)
		{
			$unit->addStatus($this->generateStatus('SamuroMirrorImageWayOfTheBlade'));
		}

		// WoI: Stack the quest
		if ($this->hasTalent('SamuroWayOfIllusion') && $unit instanceof Hero && $this->isCrit($unit) && count($this->clones))
		{
			$this->addStatus($this->generateStatus('SamuroWayOfIllusion'));
		}

		// CB: Reduce W cooldown and stack AA damage
		if ($this->hasTalent('SamuroCrushingBlow') && $unit instanceof Hero)
		{
			$this->reschedule('W', -2);

			if ($this->isCrit($unit))
			{
				$this->addStatus($this->generateStatus('SamuroCrushingBlow'));
			}
		}

		// PtA: Increase attack speed
		if ($this->hasTalent('SamuroPressTheAttack') && $unit instanceof Hero)
		{
			$this->addStatus($this->generateStatus('SamuroPressTheAttack'));
		}

		// Calculate the damage
		$damage = $this->calculateAttackDamage($unit);
		
		// Update the crit counter
		$this->nextCrit ? $this->nextCrit-- : $this->setCrit();
		
		// Reschedule this action
		$action = new Action($this, [$this, 'A'], $unit);
		$this->actions['A'] = $this->schedule()->push($this->attackPeriod(), $action);

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
		// Don't cast it if there's already a crit about to happen - better to hold and reset the next AA
		if ($this->nextCrit < 1)
		{
			$this->schedule('W', 0.1);

			return false;
		}

		// Queue a crit on next auto
		$this->setCrit(0);
		
		// Reset attack timer to half the weapon period
		if (isset($this->actions['A']))
		{
			$stamp = $this->schedule()->timestamp() + $this->attackPeriod() / 2;
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
		$this->addStatus($this->generateStatus('SamuroWindwalk'));

		// Reschedule this ability so it happens on cooldown
		$this->schedule('E', $this->hasTalent('SamuroWindStrider') ? SAMURO_COOLDOWN_E - 6 : SAMURO_COOLDOWN_E);

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
			'crush' => 0,
			'crit'  => 0,
			'spell' => 0,
			'armor' => 0,
			'harsh' => 0,
			'clone' => 0,
			'subtotal' => 0,
		];

		// Scaled base = damage * (scaling ^ level)
		$result['base'] = $this->weapons[0]->damage * pow(1 + $this->weapons[0]->damageScale, $this->level);

		// Clones have their own base
		foreach ($this->clones as $clone)
		{
			$result['clone'] += $clone->weapons[0]->damage * pow(1 + $clone->weapons[0]->damageScale, $this->level);
		}

		// Illusion Master gives clones double damage
		if ($this->hasTalent('SamuroHeroicAbilityIllusionMaster'))
		{
			$result['clone'] *= 2;
		}

		// Quest damage adds a flat amount
		if ($this->hasTalent('SamuroWayOfIllusion') && ($statusId = $this->hasStatus('SamuroWayOfIllusion')) !== null)
		{
			$status = $this->statuses[$statusId];

			$result['quest'] = $status->stacks * $status->amount;

			// If the quest is done, add the bonus
			if ($status->stacks >= $status->maxStacks)
			{
				$result['quest'] += SAMURO_QUEST_BONUS;
			}
		}
		$adjusted = $result['base'] + $result['quest'];

		// Add percent damage from Crushing Blow stacks
		if ($this->hasTalent('SamuroCrushingBlow') && ($statusId = $this->hasStatus('SamuroCrushingBlow')) !== null)
		{
			$status = $this->statuses[$statusId];

			$result['crush'] = $adjusted * $status->stacks * $status->amount;
			
			$adjusted += $result['crush'];
		}

		// Is it a critical strike?
		if ($this->isCrit($unit))
		{
			/* Handle crit modifiers from level 7 talents */

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
				$result['crit'] = $adjusted * (0.5 + (count($this->clones) * 0.45));
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
		$statusId = $unit->hasStatus('SamuroHarshWinds');
		if ($statusId !== null)
		{
			$status = $unit->statuses()[$statusId];
			$result['harsh'] = $damage * $status->amount;
		}

		// Tally it up
		$result['subtotal'] = array_sum($result);
		$result['samuro']   = $result['subtotal'] - $result['clone'];

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
	 * Set the number of stacks for Way of Illusion.
	 *
	 * @param int $num
	 *
	 * @return $this
	 */
	public function quest(int $num = 40): self
	{
		$status = $this->generateStatus('SamuroWayOfIllusion');
		$status->stacks = $num;

		$this->addStatus($status);

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
			// Grant Samuro Stealth for up to 10 seconds. While Stealthed, Samuro heals for 1% of his maximum health every second, his Movement Speed is increased by 25%
			case 'SamuroWindwalk':
				return new Status([
					'type'      => 'SamuroWindwalk',
					'amount'    => 1,
					'duration'  => 10,
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

			// Critical Strikes against enemy Heroes increase Samuro's Basic Attack damage by 15% for 4 seconds, stacking up to 3 times
			case 'SamuroCrushingBlow':
				return new Status([
					'type'      => 'SamuroCrushingBlow',
					'stacks'    => 1,
					'maxStacks' => 3,
					'amount'    => 0.15,
					'duration'  => 4,
				]);
			break;

			// While Advancing Strikes is active Samuro and his Mirror Images gain 10% Attack Speed every time they Basic Attack a Hero, up to 40%
			case 'SamuroPressTheAttack':
				return new Status([
					'type'      => 'attackSpeed',
					'stacks'    => 1,
					'maxStacks' => 4,
					'amount'    => 0.1,
					'duration'  => $this->hasTalent('SamuroBlademastersPursuit') ? 4 : 2,
				]);
			break;

			// Attacking a Hero during Wind Walk causes them to take 30% increased damage from Samuro for 3 seconds
			case 'SamuroHarshWinds':
				return new Status([
					'type'      => 'SamuroHarshWinds',
					'amount'    => 0.3,
					'duration'  => 3,
				]);
			break;

		}

		throw new \RuntimeException('Unknown status requested: ' . $name);
	}
}
