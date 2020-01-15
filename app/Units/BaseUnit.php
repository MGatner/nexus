<?php namespace App\Units;

use App\Libraries\Action;
use App\Libraries\Outcome;
use App\Libraries\Schedule;
use App\Libraries\Status;

abstract class BaseUnit
{
	/**
	 * Default data for this instance.
	 *
	 * @var object
	 */
	protected $default;

	/**
	 * Current data for this instance.
	 *
	 * @var object
	 */
	protected $current;

	/**
	 * This unit's active statuses.
	 *
	 * @var array of Status objects
	 */
	protected $statuses = [];

	/**
	 * The Schedule to use for this hero's actions.
	 *
	 * @var Queue
	 */
	private $schedule;
	
	// Lazy load data from the source
	abstract protected function ensureData();

	/**
	 * Assign a schedule.
	 *
	 * @param Schedule $schedule  The schedule to use for this unit's actions
	 *
	 * @return $this
	 */
	public function setSchedule(Schedule &$schedule): self
	{
		$this->schedule = $schedule;
		
		return $this;
	}

	/**
	 * Load (if necessary) and return this unit's schedule.
	 *
	 * @return Schedule
	 */
	public function schedule(): Schedule
	{
		if ($this->schedule === null)
		{
			$this->schedule = \Config\Services::schedule(true);
		}
		
		return $this->schedule;
	}

	/**
	 * Locate the latest patch file with validate data.
	 *
	 * @param string $file  Glob of the file to locate
	 *
	 * @return string  Path to the latest patch matching $file
	 */
    public function getPath($file): string
    {
    	if (! is_dir(HEROES_DATA_PATH))
    	{
    		throw new RuntimeException('Unable to locate Heroes data directory! Did you run "composer install"?');
    	}

		$files = glob(HEROES_DATA_PATH  . '*/data/' . $file);

    	if (! is_array($files))
    	{
    		throw new RuntimeException('Unable to locate the data file! Something is wrong with your data directory.');
    	}
    	
    	return end($files);
    }

	/**
	 * Resets data to their defaults and clears all statuses.
	 *
	 * @return $this
	 */
    public function reset(): self
    {
    	$this->current = clone $this->default;

		foreach ($this->statuses as $statusId => $status)
		{
			$this->removeStatus($statusId);
		}

    	return $this;
    }

	/**
	 * Return this unit's active Status objects.
	 *
	 * @return array
	 */
	public function statuses(): array
	{
		return $this->statuses;
	}

	/**
	 * Whether this unit has any of the requested status types.
	 *
	 * @param array|string $types  Name or names of the status type
	 *
	 * @return int|null  Index to the first matched status, or false if none were active
	 */
	public function hasStatus($types): ?int
	{
		if (! is_array($types))
		{
			$types = [$types];
		}

		foreach ($this->statuses as $i => $status)
		{
			if (in_array($status->type, $types))
			{
				return $i;
			}
		}

		return null;
	}

	/**
	 * Whether this unit has any of the requested status types.
	 *
	 * @param Status $status  The Status to apply
	 *
	 * @return int  The ID of the status just added/updated
	 */
	public function addStatus(Status $status)
	{
		// Check for an existing status of this type
		$statusId = $this->hasStatus($status->type);

		// If there is on and it is unique then we need to check stacking
		if ($status->unique && $statusId !== null)
		{
			// If it doesn't stack then remove the current one
			if ($status->stacks === null)
			{
				// If this status was on a schedule then remove it
				if ($actionId = $this->statuses[$statusId]->actionId)
				{
					$this->schedule()->cancel($actionId);
				}

				$this->removeStatus($statusId);
			}
			
			// Existing, stacking status
			else
			{
				// Update the stacks
				$this->statuses[$statusId]->stacks += $status->stacks;

				// Make sure it hasn't exceeded the maximum stacks
				if ($status->maxStacks)
				{
					$this->statuses[$statusId]->stacks = min($status->maxStacks, $this->statuses[$statusId]->stacks);
				}
				
				// If it was scheduled then refresh the duration
				if ($actionId = $this->statuses[$statusId]->actionId)
				{
					$this->schedule()->update($actionId, $this->schedule()->timestamp() + $status->duration);
				}

				return $statusId;
			}
		}
		
		/* At this point this is either a non-unique status or the old was removed */

		// Add it
		$this->statuses[] = $status;
		$statusId = array_keys($this->statuses)[count($this->statuses)-1];

		// If the status has a duration then schedule its removal
		if ($status->duration)
		{
			$action = new Action($this, [$this, 'removeStatus'], $statusId);
			$this->statuses[$statusId]->actionId = $this->schedule()->push($status->duration, $action);
		}

		return $statusId;
	}

	/**
	 * Remove and unschedule (if necessary) a status by its ID.
	 *
	 * @param int $statusId  Index to the status
	 *
	 * @return bool  Whether the status was found
	 */
	public function removeStatus(int $statusId): bool
	{
		if (isset($this->statuses[$statusId]) && $status = $this->statuses[$statusId])
		{
			unset($this->statuses[$statusId]);
			
			// Cancel any expiry action
			if ($status->actionId)
			{
				$this->schedule()->cancel($status->actionId);
			}

			return true;
		}

		return false;
	}

	/**
	 * Load (if necessary) and return a value from the data set.
	 *
	 * @param string $name  Name of the key to look for
	 *
	 * @return mixed  Value from the data set
	 */
    public function &__get(string $name)
    {
    	$this->ensureData();
    	
    	return $this->current->$name;
    }

	/**
	 * Complimentary property checker to __isset()
	 *
	 * @param string $name  Name of the key to look for
	 *
	 * @return bool  Whether the property exists
	 */
	public function __isset(string $name): bool
	{
    	$this->ensureData();

		return isset($this->current->$name);
	}

	/**
	 * Update a value in current data.
	 *
	 * @param string $name   Name of the key to change
	 * @param string $value  New value for $name
	 *
	 * @return $this
	 */
    public function __set(string $name, $value): self
    {
    	$this->ensureData();
    	
    	$this->current->$name = $value;
    	
    	return $this;
    }
}
