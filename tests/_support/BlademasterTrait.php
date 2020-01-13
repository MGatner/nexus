<?php namespace ProjectTests\Support;

use App\Libraries\Units\Blademaster;

trait BlademasterTrait
{
	/**
	 * Force other cooldowns so next action will be the specified one
	 *
	 * @param Blademaster $blademaster  The instance to update
	 * @param string $action  Hotkey for the action to force
	 */
	protected function setNextAction(Blademaster $blademaster, $action)
	{
		$cooldowns = [
			'A' => 0.6,
			'Q' => 14,
			'W' => 10,
			'E' => 15,
			'R' => 25,
			'D' => 25,
		];
		
		$cooldowns[$action] = 0;

		$this->setPrivateProperty($blademaster, 'cooldowns', $cooldowns);
	}

	/**
	 * Force the next critical attack value
	 *
	 * @param Blademaster $blademaster  The instance to update
	 * @param int $num  Number of attacks until next crit
	 */
	protected function setNextCrit(Blademaster $blademaster, int $num = 0)
	{
		$this->setPrivateProperty($blademaster, 'nextCrit', $num);
	}
}
