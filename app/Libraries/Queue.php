<?php namespace App\Libraries;

use App\Libraries\Action;

/**
 * Class Queue
 *
 * Queue to handle registered actions.
 */
class Queue
{
	/**
	 * Time limit before the simulator quits.
	 *
	 * @var float
	 */
	public $timelimit = 60;

	/**
	 * Simulated seconds since the queue started.
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
	protected $schedule = [];

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
	 * @param float $time         Seconds in the future to schedule the action
	 * @param Action $action    The action to perform
	 *
	 * @return int  ID of the registered action in the queue
	 */
	public function push(float $time, Action $action): int
	{
		// Get the next ID
		$this->lastId++;

		// Register the action
		$this->ids[]     = $this->lastId;
		$this->actions[] = $action;

		// Schedule it
		$this->schedule[] = $this->timestamp + $time;

		// Notify that the schedule is no longer necessarily in order
		$this->sorted = false;
		
		return $this->lastId;
	}

	/**
	 * Return the next Action, rescheduling repeats and increasing timestamp as necessary.
	 *
	 * @return Action  The next scheduled Action in the queue
	 */
	public function pop(): ?Action
	{
		if (empty($this->schedule))
		{
			return null;
		}

		// Sort the queue and metadata
		array_multisort($this->schedule, $this->actions, $this->ids);
		$this->sorted = true;

		// Get what's next
		$stamp  = array_shift($this->schedule);
		$action = array_shift($this->actions);
		$id     = array_shift($this->ids);
		
		// Push forward the current time
		$this->timestamp = $stamp;

		return $action;
	}
}
