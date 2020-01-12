<?php namespace App\Models;

class AbilityModel extends BaseModel
{
	protected $table = 'abilities';

	protected $allowedFields = [
		'nameId', 'buttonId', 'icon', 'abilityType', 'charges',
		'countMax', 'countUse', 'countStart', 'countStart', 'hideCount', 'recastCooldown',
	];
}
