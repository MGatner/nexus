<?php namespace App\Libraries\Units;

use App\Libraries\Properties\Stats;

/**
 * Class Hero
 *
 * A unit of type hero.
 */
class Hero extends Unit
{
	/**
	 * Hero abilities.
	 *
	 * @var object
	 */
	protected $abilities;

	/**
	 * The current cooldown for each ability.
	 *
	 * @var array
	 */
	protected $cooldowns;

	/**
	 * All this hero's talents.
	 *
	 * @var object
	 */
	protected $talents;

	/**
	 * Talents this hero has selected.
	 *
	 * @var array
	 */
	public $talented = [];

	//--------------------------------------------------------------------
	// Statuses
	//--------------------------------------------------------------------

	/**
	 * Current level of the hero.
	 *
	 * @var int
	 */
	public $level = 1;

	/**
	 * Current physical armor.
	 *
	 * @var int
	 */
	public $physicalArmor = 0;

	/**
	 * Current spell armor.
	 *
	 * @var int
	 */
	public $spellArmor = 0;

	/**
	 * Array of active effects.
	 * 	- slow
	 * 	- stun
	 * 	- dead
	 * 	- {various} marks for specific talents/abilities (like Harsh Wind)
	 *
	 * @var array
	 */
	public $effects = [];

	/**
	 * Create the hero with an intial set of values.
	 *
	 * @param Stats  $stats
	 */
	public function __construct(Stats $stats = null)
	{
		parent::__construct('hero', $stats);
		
		// WIP - need setters for these
		$this->abilities = $abilities ?? new \stdClass();
		$this->talents   = $talents ?? new \stdClass();
		
		// WIP - Start cooldowns at 0
		$this->cooldowns = [
			'A' => 0,
			'Q' => 0,
			'W' => 0,
			'E' => 0,
			'R' => 0,
			'D' => 0,
		];
	}

	/**
	 * Set this hero's abilities.
	 *
	 * @param array $abilities  Array of abilities as they come from the database
	 *
	 * @return $this
	 */
	public function setAbilities(array $abilities): self
	{
		$this->abilities = $abilities;
		
		return $this;
	}

	/**
	 * Set this hero's talents.
	 *
	 * @param array $talents  Array of talents as they come from the database
	 *
	 * @return $this
	 */
	public function setTalents(array $talents): self
	{
		$this->talents = $talents;
		
		return $this;
	}

	/**
	 * Whether this hero has the selected the target talent.
	 *
	 * @param string $nameId  nameId of the target talent
	 *
	 * @return bool
	 */
	public function hasTalent(string $nameId): bool
	{
		return in_array($nameId, $this->talented);
	}

	/**
	 * Whether this hero has any of the requested effects.
	 *
	 * @param array|string $effect  Name or names of the effect
	 *
	 * @return bool  Whether any of the effects are active
	 */
	public function hasEffect(string $effect): bool
	{
		if (! is_array($effect))
		{
			$effect = [$effect];
		}
		
		return (bool) array_intersect($effect, $this->effects);
	}
}
