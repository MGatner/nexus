<?php namespace App\Models;

use CodeIgniter\Model;

class StatModel extends Model
{
	protected $table      = 'stats';
	protected $primaryKey = 'id';
	protected $returnType = 'object';

	protected $useTimestamps  = true;
	protected $useSoftDeletes = false;
	
	protected $allowedFields  = ['name', 'type', 'value'];
	protected $skipValidation = true;
}
