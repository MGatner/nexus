<?php namespace App\Libraries;

use App\Units\BaseUnit;

/**
 * Class Action
 *
 * An action for a unit to enact.
 */
class Action
{
	/**
	 * The unit performing the action.
	 *
	 * @var Unit
	 */
	public $unit;

	/**
	 * Seconds in the future to schedule the action
	 *
	 * @var float
	 */
	protected $time;

	/**
	 * The actual method to call.
	 *
	 * @var callback
	 */
	protected $callback;

	/**
	 * Save the parameters.
	 *
	 * @param Unit $unit          The unit issuing this action
	 * @param callback $callback  The callback to perform
	 * @param float $time         Seconds in the future to schedule the action
	 */
	public function __construct(BaseUnit &$unit, $callback, float $time)
	{
		$this->unit     = $unit;
		$this->callback = $callback;
		$this->time     = $time;
	}

	/**
	 * Return the scheduled time.
	 *
	 * @return float
	 */
	public function time(): float
	{
		return $this->time;
	}

	/**
	 * Run the callback and return the results.
	 *
	 * @return array|null
	 */
	public function run(): ?array
	{
		return call_user_func($this->callback);
	}
}
