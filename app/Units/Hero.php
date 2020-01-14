<?php namespace App\Units;

use App\Libraries\Queue;

/**
 * Class Hero
 *
 * A unit of type hero.
 */
class Hero extends BaseUnit
{
	/**
	 * Master set of all herodata.
	 *
	 * @var object
	 */
	protected static $master;

	/**
	 * Unique hero identifier.
	 *
	 * @var string
	 */
	protected $cHeroId;

	/**
	 * The queue to use for this hero's actions.
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * Talents this hero has selected.
	 *
	 * @var array
	 */
	public $talented;

	/**
	 * IDs for active Actions for each ability
	 *
	 * @var array
	 */
	public $actions = [];

	//--------------------------------------------------------------------
	// Statuses
	//--------------------------------------------------------------------

	/**
	 * Current level of the hero.
	 *
	 * @var int
	 */
	public $level;

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
	 * @var array of type => value
	 */
	public $effects = [];

	/**
	 * Create the hero with an intial set of values.
	 *
	 * @param string $cHeroId  The ID of the hero to load
	 * @param Queue $queue     The queue to use for this hero's actions
	 * @param int $level       Hero's initial level
	 * @param array $talented  Hero's initial talent selection
	 */
	public function __construct(string $cHeroId, Queue &$queue, int $level = 1, array $talented = [])
	{
		$this->cHeroId  = $cHeroId;
		$this->queue    = $queue;
		$this->level    = $level;
		$this->talented = $talented;
	}

	/**
	 * Lazy load data from the source.
	 */
	protected function ensureData(): void
	{
		// If the data is there then all is well
		if ($this->default)
    	{
    		return;
    	}

		// Make sure the data is loaded from file
		if (self::$master === null)
		{
			$path = $this->getPath('herodata_*.json');
			$json = file_get_contents($path);
			self::$master = json_decode($json);
		}
		
		// Point this instance's data at the specified hero
		$this->default =& self::$master->{$this->cHeroId};
		
		// Clone the default to start the current set
		$this->current = clone $this->default;
	}

	/**
	 * Whether this hero has selected the target talent.
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
	public function hasEffect($effect): bool
	{
		if (! is_array($effect))
		{
			$effect = [$effect];
		}
		
		return (bool) array_intersect($effect, array_keys($this->effects));
	}
}
