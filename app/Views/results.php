<?= $this->extend('layout') ?>
<?= $this->section('main') ?>

<h2>Results</h2>

<div class="row">
	<p><?= implode(', ', $talents) ?></p>
	<div class="col">
		<?= $table ?>
	</div>
</div>
