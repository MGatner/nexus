<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

	<!--Mobile meta-data -->
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<meta name="description" content="Nexus Simulator" />
	<meta name="keywords" content="nexus,simulator,heroes,storm,blizzard" />
	<meta name="author" content="Tatter Software" />
	<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

	<title>Nexus Simulator</title>

	<!-- Favicon
	<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('assets/favicon/apple-touch-icon.png') ?>">
	<link rel="icon" type="image/png" sizes="32x32" href="<?= base_url('assets/favicon/favicon-32x32.png') ?>">
	<link rel="icon" type="image/png" sizes="16x16" href="<?= base_url('assets/favicon/favicon-16x16.png') ?>">
	<link rel="manifest" href="<?= base_url('assets/favicon/site.webmanifest') ?>">
	<link rel="mask-icon" href="<?= base_url('assets/favicon/safari-pinned-tab.svg') ?>" color="#012169">
	<link rel="shortcut icon" href="<?= base_url('assets/favicon/favicon.ico') ?>">
	<meta name="msapplication-TileColor" content="#2b5797">
	<meta name="msapplication-config" content="<?= base_url('assets/favicon/browserconfig.xml') ?>">
	<meta name="theme-color" content="#e07a42">
	-->

	<?= service('assets')->css() ?>

	<?= service('alerts')->css() ?>

	<?= service('assets')->tag('vendor/jquery/jquery.min.js') ?>

	<?= view('\Tatter\Themes\Views\css') ?>
	
	<?= $this->renderSection('headerAssets') ?>

</head>
<body>
	<nav class="navbar navbar-expand-md navbar-dark bg-orange">
		<a class="navbar-brand" href="<?= site_url() ?>">Nexus Simulator</a>

		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>

		<div class="collapse navbar-collapse" id="navbarSupportedContent">
			<ul class="navbar-nav ml-auto text-center">
				<li class="nav-item">
					<a class="navbar-text mr-sm-3" href="<?= site_url('home/theme') ?>"><i class="fas fa-moon mr-1"></i>Theme</a>
				</li>
			</ul>
    	</div>
	</nav>

	<?= service('alerts')->display() ?>
	
	<main role="main" class="container pb-5">

		<?= $this->renderSection('main') ?>

	</main>

	<footer class="footer">

	</footer>

	<script>
		var baseUrl = "<?= base_url() ?>";
		var siteUrl = "<?= site_url() ?>";
		var apiUrl  = "<?= site_url('api/') ?>";
	</script>

	<?= service('assets')->js() ?>

	<?= $this->renderSection('footerAssets') ?>
</body>
</html>
