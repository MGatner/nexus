<?php namespace App\Models;

class TalentModel extends BaseModel
{
	protected $table = 'talents';
	
	protected $allowedFields = [
		'nameId', 'buttonId', 'icon',
		'abilityType', 'isQuest', 'sort',
	];
}
