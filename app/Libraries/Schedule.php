<?php namespace App\Libraries;

use App\Libraries\Action;
use App\Libraries\Outcome;

/**
 * Class Schedule
 *
 * Schedule to handle registered actions.
 */
class Schedule
{
	/**
	 * Time limit before the simulator quits.
	 *
	 * @var float
	 */
	public $timelimit = 60;

	/**
	 * Simulated seconds since the game started.
	 *
	 * @var float
	 */
	protected $timestamp = 0;

	/**
	 * Last ID used to register an action.
	 *
	 * @var int
	 */
	protected $lastId = 0;

	/**
	 * Timestamps to run each registered action.
	 *
	 * @var array of floats
	 */
	protected $stamps = [];

	/**
	 * Registered actions.
	 *
	 * @var array of Actions
	 */
	protected $actions = [];

	/**
	 * Identifiers for registered actions.
	 *
	 * @var array of ints
	 */
	protected $ids = [];

	/**
	 * Return the current simulated timestamp.
	 *
	 * @return float
	 */
	public function timestamp(): float
	{
		return $this->timestamp;
	}

	/**
	 * Register an action to run at a time in the future.
	 *
	 * @param float $time     Seconds in the future to schedule the action
	 * @param Action $action  The action to perform
	 *
	 * @return int  ID of the registered action in the Schedule
	 */
	public function push(float $time, Action $action): int
	{
		// Get the next ID
		$this->lastId++;

		// Register the action
		$this->ids[]     = $this->lastId;
		$this->actions[] = $action;

		// Schedule it
		$this->stamps[] = $this->timestamp + $time;

		// Notify that the schedule is no longer necessarily in order
		$this->sorted = false;
		
		return $this->lastId;
	}

	/**
	 * Return the Outcome of the next Action, rescheduling repeats and increasing timestamp as necessary.
	 *
	 * @return Outcome  Outcome of the next scheduled Action
	 */
	public function pop(): ?Outcome
	{
		if (empty($this->actions))
		{
			return null;
		}
		if ($this->timestamp > $this->timelimit)
		{
			return null;
		}
		if ($this->lastId > MAX_LOOPS)
		{
			throw new \RuntimeException('Guard triggered to prevent infinite loops');
		}

		// Sort the Schedule and metadata
		array_multisort($this->stamps, $this->actions, $this->ids);
		$this->sorted = true;

		// Get what's next
		$stamp  = array_shift($this->stamps);
		$action = array_shift($this->actions);
		$id     = array_shift($this->ids);
		
		// Push forward the current time
		$this->timestamp = $stamp;

		// Run the Action and put the results into an Outcome
		$data = $action->run();
		
		return new Outcome($this->timestamp, $action->unit, $data);
	}
}
