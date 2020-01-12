<?php namespace App\Models;

use CodeIgniter\Model;

class BaseModel extends Model
{
	protected $primaryKey     = 'id';
	protected $returnType     = 'object';
	protected $useTimestamps  = true;
	protected $useSoftDeletes = true;
}
