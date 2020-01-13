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
	 * Whether the schedule can be trusted to be in order.
	 *
	 * @var bool
	 */
	protected $sorted = true;

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
	 * Instructions on repeating each action.
	 *
	 * @var int|bool  Number occurences, true for infinite, false or 0 for none
	 */
	protected $repeats = [];

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
	 * @param Action $action    The action to perform
	 * @param int|bool $repeat  Whether the action repeats; false = no repeat, true = indefinitely, int = number of times
	 *
	 * @return int  ID of the registered action in the queue
	 */
	public function push(Action $action, $repeat = false): int
	{
		// Get the next ID
		$this->lastId++;

		// Register the action
		$this->ids[]     = $this->lastId;
		$this->actions[] = $action;

		// Schedule it
		$this->schedule[] = $this->timestamp + $action->time();
		$this->repeats[]  = $repeat;

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
		array_multisort($this->schedule, $this->actions, $this->ids, $this->repeats);
		$this->sorted = true;

		// Get what's next
		$stamp  = array_shift($this->schedule);
		$action = array_shift($this->actions);
		$id     = array_shift($this->ids);
		$repeat = array_shift($this->repeats);
		
		// Push forward the current time
		$this->timestamp = $stamp;
		
		// If this action repeats then reschedule it
		if ($repeat)
		{
			// For limited re-occurences decrement the count
			if (is_int($repeat))
			{
				$repeat--;
			}

			// Make sure occurrences aren't exhausted
			if ($repeat)
			{
				$this->push($action, $repeat);
			}
		}

		return $action;
	}
}
