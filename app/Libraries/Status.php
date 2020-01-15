<?php namespace App\Libraries;

use App\Units\BaseUnit;
use App\Libraries\Outcome;

/**
 * Class Status
 *
 * A status that can be applied to a unit.
 */
class Status
{
	/**
	 * General category to check hasStatus() against.
	 * Examples: slow, stun, dead, or various marks for specific talents/abilities
	 *
	 * @var string
	 */
	public $type;

	/**
	 * Whether this status can have only one active instance per unit
	 *
	 * @var bool
	 */
	public $unique = true;

	/**
	 * Current number of stacks, null for non-stacking statuses
	 *
	 * @var int|null
	 */
	public $stacks;

	/**
	 * Maximum number of stacks
	 *
	 * @var int|null
	 */
	public $maxStacks;

	/**
	 * Value for variable amount statuses
	 *
	 * @var float|null
	 */
	public $amount;

	/**
	 * How long this status lasts before expiring
	 *
	 * @var float|null
	 */
	public $duration;

	/**
	 * ID of the associated action if this status exists on a schedule
	 *
	 * @var int|null
	 */
	public $actionId;

	/**
	 * Save each property.
	 *
	 * @param array $data
	 */
	public function __construct(array $data = [])
	{
		foreach ($data as $key => $value)
		{
			$this->$key = $value;
		}
	}
}
