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
	public $talented = [];

	/**
	 * Current level of the hero.
	 *
	 * @var int
	 */
	public $level;

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

		$this->talents($talented);
	}

	/**
	 * Lazy load data from the source.
	 */
	protected function ensureData(): void
	{
		// If the data is there then all is well
		if ($this->data)
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

		// Clone the master data to the current set
		/*
		* WIP - `clone` doesn't dereference sub-objects, hack it with serializing for now
		* $this->data = clone self::$master->{$this->cHeroId};
		*/
		$this->data = unserialize(serialize(self::$master->{$this->cHeroId}));
	}

	/**
	 * Resets data to their defaults and clears all statuses.
	 *
	 * @return $this
	 */
    public function reset()
    {
    	$this->data = unserialize(serialize(self::$master->{$this->cHeroId}));

		$this->talented = [];
		
		foreach ($this->statuses as $statusId => $status)
		{
			$this->removeStatus($statusId);
		}

    	return $this;
    }

	/**
	 * Returns this hero's cHeroId.
	 *
	 * @return string
	 */
    public function name(): string
    {
    	return $this->cHeroId;
    }

	/**
	 * Add each talent to the list of selected talents.
	 *
	 * @param string $nameId  nameId of the target talent
	 *
	 * @return $this
	 */
	public function talents(array $talents)
	{
		$this->talented = [];

		foreach ($talents as $nameId)
		{
			$this->selectTalent($nameId);
		}

		return $this;
	}

	/**
	 * Add a talent to the list of selected talents.
	 * Should be overridden by individual heroes for specifics.
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
