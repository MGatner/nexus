<?php namespace Config;

use App\Libraries\Queue;
use App\Libraries\Nexus;
use CodeIgniter\Config\Services as CoreServices;
use CodeIgniter\Config\BaseConfig;

require_once SYSTEMPATH . 'Config/Services.php';

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends CoreServices
{
	/**
	 * Returns an instance of the Nexus
	 *
	 * @param ActionQueue $queue  The Queue to handle actions
	 * @param boolean  $getShared
	 *
	 * @return \Tatter\Stripe\Stripe
	 */
	public static function nexus(Queue $queue = null, bool $getShared = true): Nexus
	{
		if ($getShared)
		{
			return static::getSharedInstance('nexus', $queue);
		}

		return new Nexus($queue);
	}
}
