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
	public $timelimit = 30;

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
	 * If passed an action ID return that action's timestamp instead.
	 *
	 * @return float
	 */
	public function timestamp(int $actionId = null): float
	{
		if ($actionId === null)
		{
			return $this->timestamp;
		}
		
		// Look up the index for this action
		$i = array_search($actionId, $this->ids);
		
		return $this->stamps[$i];
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

	/**
	 * Get the timestamp for the scheduled action.
	 *
	 * @param int $actionId  ID of the scheduled action to cancel
	 *
	 * @return float  Timestamp of the scheduled action
	 */
	public function read(int $actionId): float
	{
		$i = array_search($actionId, $this->ids);
		if ($i === false)
		{
			return false;
		}

		return $this->stamps[$i];
	}

	/**
	 * Cancel an action.
	 *
	 * @param int $actionId  ID of the scheduled action to cancel
	 *
	 * @return bool  false if the action ID wasn't found, true otherwise
	 */
	public function cancel(int $actionId): bool
	{
		$i = array_search($actionId, $this->ids);
		if ($i === false)
		{
			return false;
		}

		unset($this->ids[$i], $this->actions[$i], $this->stamps[$i]);

		return true;
	}

	/**
	 * Update the time before a scheduled action runs.
	 *
	 * @param int $actionId  ID of the scheduled action to extend
	 * @param float $stamp   New timestamp to apply
	 *
	 * @return bool  false if the action ID wasn't found, true otherwise
	 */
	public function update(int $actionId, float $stamp): bool
	{
		$i = array_search($actionId, $this->ids);

		if ($i === false)
		{
			return false;
		}

		$this->stamps[$i] = $stamp;

		return true;
	}
}
