<?php namespace App\Units;

/**
 * Class Unit
 *
 * A non-hero unit, relevant for PvE.
 */
class Unit extends BaseUnit
{
	/**
	 * Master set of all unitdata.
	 *
	 * @var object
	 */
	protected static $master;

	/**
	 * Unique unit identifier.
	 *
	 * @var string
	 */
	protected $cUnitId;

	/**
	 * Create the unit with an intial set of values.
	 *
	 * @param string $cUnitId     The ID of the unit to load
	 */
	public function __construct(string $cUnitId)
	{
		$this->cUnitId  = $cUnitId;
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
			$path = $this->getPath('unitdata_*.json');
			$json = file_get_contents($path);
			self::$master = json_decode($json);
		}

		// Clone the master data to the current set
		/*
		* WIP - `clone` doesn't dereference sub-objects, hack it with serializing for now
		* $this->data = clone self::$master->{$this->cHeroId};
		*/
		$this->data = unserialize(serialize(self::$master->{$this->cUnitId}));
	}

	/**
	 * Resets data to their defaults and clears all statuses.
	 *
	 * @return $this
	 */
    public function reset()
    {
    	$this->data = unserialize(serialize(self::$master->{$this->cUnitId}));
		
		foreach ($this->statuses as $statusId => $status)
		{
			$this->removeStatus($statusId);
		}

    	return $this;
    }

	/**
	 * Returns this unit's cUnitId.
	 *
	 * @return string
	 */
    public function name(): string
    {
    	return $this->cUnitId;
    }
}
