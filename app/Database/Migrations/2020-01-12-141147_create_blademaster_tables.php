<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlademasterTables extends Migration
{

	public function up()
	{
		// Hero statistics
		$fields = [
			'name'       => ['type' => 'varchar', 'constraint' => 63],
			'type'       => ['type' => 'varchar', 'constraint' => 63],
			'value'      => ['type' => 'float'],
			'created_at' => ['type' => 'datetime', 'null' => true],
			'updated_at' => ['type' => 'datetime', 'null' => true],
		];
		
		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addKey(['type', 'name']);
		$this->forge->addKey('created_at');
		
		$this->forge->createTable('stats');

		// Abilities
		$fields = [
			'nameId'         => ['type' => 'varchar', 'constraint' => 63],
			'buttonId'       => ['type' => 'varchar', 'constraint' => 63],
			'icon'           => ['type' => 'varchar', 'constraint' => 63],
			'charges'        => ['type' => 'bool', 'default' => 0],
			'countMax'       => ['type' => 'int', 'null' => true],
			'countUse'       => ['type' => 'int', 'null' => true],
			'countStart'     => ['type' => 'int', 'null' => true],
			'countStart'     => ['type' => 'varchar', 'constraint' => 63],
			'hideCount'      => ['type' => 'bool', 'default' => 1],
			'recastCooldown' => ['type' => 'float', 'null' => true],
			'abilityType'    => ['type' => 'varchar', 'constraint' => 7],
			'created_at'     => ['type' => 'datetime', 'null' => true],
			'updated_at'     => ['type' => 'datetime', 'null' => true],
			'deleted_at'     => ['type' => 'datetime', 'null' => true],
		];

		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addKey('nameId');
		$this->forge->addKey('buttonId');
		$this->forge->addKey(['deleted_at', 'id']);
		
		$this->forge->createTable('abilities');

		// Talents
		$fields = [
			'nameId'         => ['type' => 'varchar', 'constraint' => 63],
			'buttonId'       => ['type' => 'varchar', 'constraint' => 63],
			'icon'           => ['type' => 'varchar', 'constraint' => 63],
			'abilityType'    => ['type' => 'varchar', 'constraint' => 7],
			'isQuest'        => ['type' => 'bool', 'default' => 0],
			'sort'           => ['type' => 'int'],
			'created_at'     => ['type' => 'datetime', 'null' => true],
			'updated_at'     => ['type' => 'datetime', 'null' => true],
			'deleted_at'     => ['type' => 'datetime', 'null' => true],
		];

		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addKey('nameId');
		$this->forge->addKey('buttonId');
		$this->forge->addKey(['deleted_at', 'id']);
		
		$this->forge->createTable('talents');
		
		// Abilities <-> Talents
		$fields = [
			'ability_id' => ['type' => 'int'],
			'talent_id'  => ['type' => 'int'],
			'created_at' => ['type' => 'datetime', 'null' => true],
		];
		
		$this->forge->addField('id');
		$this->forge->addField($fields);

		$this->forge->addUniqueKey(['ability_id', 'talent_id']);
		$this->forge->addUniqueKey(['talent_id', 'ability_id']);
		$this->forge->addForeignKey('ability_id', 'abilities', 'id', false, 'CASCADE');
		$this->forge->addForeignKey('talent_id', 'talents', 'id', false, 'CASCADE');
		
		$this->forge->createTable('abilities_talents');
	}

	public function down()
	{
		$this->forge->dropTable('stats');
		$this->forge->dropTable('abilities');
		$this->forge->dropTable('talents');
		$this->forge->dropTable('abilities_talents');
	}
}
