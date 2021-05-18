<?php namespace App\Controllers;

use App\Libraries\Status;
use App\Units\Hero;
use App\Units\Unit;
use App\Units\Heroes\Samuro;

class Simulate extends BaseController
{
	public function samuro()
	{
		helper('form');

		$data = [
			'hero' => new Samuro(),
		];

		return view('simulate', $data);
	}

	public function results()
	{
		$post = $this->request->getPost();
		
		// Strip everything that isn't a talent
		$hero   = $post['hero'];
		$level  = $post['level'];
		$target = $post['target'] ?? 'Raynor';
		unset($post['hero'], $post['level'], $post['target']);

		// WIP - needs to check for implemented hero class \App\Units\Heroes\{$hero}
		$samuro = new Samuro($level, $post);

		$unit = $target == 'Raynor' ? new Hero($target) : new Unit($target);

		// Pre-cast abilities in the desired order
		$samuro->setCrit(0);
		$samuro->Q();
		$samuro->E();

		// If WoI was selected then complete the quest
		if ($samuro->hasTalent('SamuroWayOfIllusion'))
		{
			$samuro->quest();
		}
		// If Merciless was selected then slow the target (if it is a hero)
		if ($samuro->hasTalent('SamuroMercilessStrikes') && $unit instanceof Hero)
		{
			$unit->addStatus(new Status([
				'type'      => 'slow',
				'stacks'    => 1,
				'amount'    => 0.20,
			]));
		}

		// Schedule the first attack then W immediately after
		$samuro->schedule('A', 0, $unit);
		$samuro->schedule('W', 0.1);

		// Start the HTML table
		$table = new \CodeIgniter\View\Table();
		$table->setHeading(['Base', 'Quest', 'Crush', 'Crit', 'Spell', 'Armor', 'Harsh', 'Clone', 'CCrit', 'CSpell', 'Subtotal', 'Samuro', 'Timestamp', 'ID', 'SamTotal', 'Total']);

		// Run the schedule, adding outcomes as rows
		$total    = 0;
		$samTotal = 0;
		$count    = 1;
		while ($outcome = $samuro->schedule()->pop())
		{
			if ($outcome->keep)
			{
				$row = $outcome->data;
				$row['time']  = $outcome->timestamp;
				$row['count'] = $count;

				// Round all values
				$row = array_map(function($num) { return round($num, 2); }, $row);

				$samTotal += $row['samuro'];
				$row['samTotal'] = $samTotal;

				$total += $row['subtotal'];
				$row['total'] = $total;
				$count++;

				$table->addRow($row);

				$count++;
			}
		}

		$data = [
			'hero'    => $hero,
			'level'   => $level,
			'target'  => $target,
			'talents' => $post,
			'samuro'  => $samuro,
			'unit'    => $unit,
			'table'   => $table->generate(),
		];

		return view('results', $data);		
	}
}
