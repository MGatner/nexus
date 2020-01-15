<?php namespace App\Units;

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
	 * Create the hero with an intial set of values.
	 *
	 * @param string $cHeroId     The ID of the hero to load
	 * @param int $level          Hero's initial level
	 * @param array $talented     Hero's initial talent selection
	 */
	public function __construct(string $cHeroId, int $level = 1, array $talented = [])
	{
		$this->cHeroId  = $cHeroId;
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
		$this->default = self::$master->{$this->cHeroId};
		
		// Clone the default to start the current set
		$this->current = clone $this->default;
	}

	/**
	 * Add a talent to the list of selected talents.
	 * Should be overridden by indivudal heroes for specifics.
	 *
	 * @param string $nameId  nameId of the target talent
	 *
	 * @return $this
	 */
	public function selectTalent(string $nameId)
	{
		$this->talented[] = $nameId;

		return $this;
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
}
