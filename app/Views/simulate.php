<?= $this->extend('layout') ?>
<?= $this->section('main') ?>

<h2><?= $hero->name() ?></h2>

<div class="row">
	<div class="col">
		<?= form_open('simulate/results') ?>

			<input name="hero" type="hidden" value="<?= $hero->name() ?>" />

			<div class="form-group">
				<label for="level">Level <span id="level-value">20</span></label>
				<input class="form-control-range" id="level" name="level" type="range" min="1" max="30" step="1" value="<?= old('level', 20) ?>" onchange="$('#level-value').html(this.value);">
			</div>

			<div class="form-group">
				<label for="target">Target</label>
					<select class="custom-select" name="target" disabled>
						<option selected>Raynor</option>
					</select>
				</select>
			</div>

			<h3>Talents</h3>
			<div class="card-deck">

				<?php foreach ($hero->talents as $level => $talents): ?>

				<div class="card mb-4" style="min-width: 18rem; max-width: 18rem;">
					<div class="card-body">
						<h5 class="card-title"><?= $level ?></h5>
						<ul class="list-group list-group-flush">
					
						<?php foreach ($talents as $talent): ?>

							<li class="list-group-item">
								<div class="custom-control custom-radio">
									<input type="radio" id="<?= $talent->nameId ?>" name="<?= $level ?>" class="custom-control-input" value="<?= $talent->nameId ?>">
									<label class="custom-control-label" for="<?= $talent->nameId ?>"><?= str_replace('Samuro', '', $talent->nameId) ?></label>
								</div>
							</li>

						<?php endforeach; ?>

						</ul>
					</div>
				</div>
				
				<?php endforeach; ?>

			</div>

			<button type="submit" class="btn btn-primary">Launch</button>

		<?= form_close() ?>

	</div>
</div>

<?= $this->endSection() ?>
