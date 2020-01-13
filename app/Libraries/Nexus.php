<?php namespace App\Libraries;

use App\Libraries\Action;
use App\Libraries\Queue;
use App\Libraries\Outcome;

/**
 * Class Nexus
 *
 * Main service for the simulator.
 */
class Nexus
{
	/**
	 * Time limit before the simulator quits.
	 *
	 * @var float
	 */
	public $limit = 60;

	/**
	 * Handler for registered actions.
	 *
	 * @var Queue
	 */
	protected $queue;

	/**
	 * Record of action outcomes.
	 *
	 * @var array of Outcomes
	 */
	protected $outcomes;

	/**
	 * Initialize the simulator.
	 *
	 * @param array  $data
	 */
	public function __construct(Queue $queue)
	{
		$this->queue = $queue;
	}

	/**
	 * Return the handler for queued actions.
	 *
	 * @return ActionQueue
	 */
	public function queue(): Queue
	{
		return $this->queue;
	}

	/**
	 * Return all recorded outcomes.
	 *
	 * @return array
	 */
	public function getOutcomes(): array
	{
		return $this->outcomes;
	}

	/**
	 * Run the next Action and record the outcome.
	 *
	 * @return bool  Whether anything ran
	 */
	public function run(): bool
	{
		// Get the next action off the queue
		$action = $this->queue->pop();
		
		if ($action === null)
		{
			return false;
		}
		
		// Don't run actions past the time limit
		$stamp = $this->queue->timestamp();
		if ($stamp > $this->limit)
		{
			return false;
		}
		
		// Run the action
		$data = $action->run();
		
		// Save the outcome
		$this->outcomes[] = new Outcome($stamp, $action->unit, $data);
		
		return true;
	}
}
