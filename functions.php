<?php

$page = array();

try {
	$db = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'], $config['db']['user'], $config['db']['password']);
} catch (PDOException $error) {
	get_error(500, 'Virhe tietokantayhteydessä.');
}

function page_setup() {
	global $page;

	$path = (!isset($_GET['path']) || empty($_GET['path']) ? '' : $_GET['path']);

	switch ($path) {
		case '':
			$page['title'] = 'Kisakanta';
			$page['content'] = 'Tervetuloa kiskaan';
			break;
		case 'tietoa':
			$page['title'] = 'Tietoa';
			$page['content'] = 'Tietoa palvelusta';
			break;
		case (preg_match('/kisat\/(?<slug>[\-a-zA-Z0-9]+)/', $path, $matches) ? true : false) :
			get_contest($matches['slug']);
			break;
		case 'kisat':
			$page['title'] = 'Kilpailut';
			$page['content'] = 'Palveluun lisätyt kilpailut';
			break;
		case (preg_match('/tehtavat\/(?<id>[0-9]+)/', $path, $matches) ? true : false) :
			get_task($matches['id']);
			break;
		case 'tehtavat':
			$page['title'] = 'Tehtävät';
			$page['content'] = 'Palveluun lisätyt tehtävät';
			break;
		default:
			get_error(404);
			break;
	}
}

function get_header() {
	global $page;
	?><!doctype html>
	<html class="no-js" lang="fi">
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
			<title><?php echo $page['title']; ?></title>
			<meta name="description" content="">
			<meta name="viewport" content="width=device-width, initial-scale=1">

			<link rel="stylesheet" href="/assets/css/main.css">

			<script src="/assets/js/vendor/modernizr-2.8.3.min.js"></script>
		</head>
		<body>
			<header id="site-header">

				<a rel="home" href="/" title="Etusivu" class="site-title">Kisakanta</a>

				<nav>
					<ul>
						<li><a href="/kisat">Kilpailut</a></li>
						<li><a href="/tehtavat">Tehtävät</a></li>
						<li><a href="/tietoa">Tietoa</a></li>
					</ul>
				</nav>

			</header>

			<main id="content" role="main">
	<?php
}

function get_footer() {
	?>
			</main>

			<script src="/assets/js/main.js"></script>

			<!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
			<script>
				(function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
				function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
				e=o.createElement(i);r=o.getElementsByTagName(i)[0];
				e.src='//www.google-analytics.com/analytics.js';
				r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
				ga('create','UA-XXXXX-X','auto');ga('send','pageview');
			</script>
		</body>
	</html>
	<?php
}

function get_content() {
	global $page;
	?>
	<article>
		<h1><?php echo $page['title']; ?></h1>
		<?php echo $page['content']; ?>
	</article>
	<?php
}

function get_contest($slug) {
	global $db, $page;

	$query = $db->prepare('SELECT * FROM kilpailu WHERE id = ?');
	$query->execute(array($slug));

	if($query->rowCount() > 0) {
		$contest = $query->fetch();
		$page['title'] = $contest['nimi'];
		$page['content'] = $contest['kisapaikka'];
	} else {
		get_error(404);
	}
}

function get_task($id) {
	global $db, $page;

	$query = $db->prepare('SELECT * FROM tehtava WHERE id = ?');
	$query->execute(array($id));

	if($query->rowCount() > 0) {
		$task = $query->fetch();
		$page['title'] = $task['nimi'];
		$page['content'] = $task['kasky'];
	} else {
		get_error(404);
	}
}

function get_error($code = 404, $msg = 'Sivua ei löytynyt.') {
	global $page;
	
	header('Error', true, $code);

	$page['title'] = 'Virhe ' . $code;
	$page['content'] = '<p>' . $msg . '</p><p><a href="/">Palaa etusivulle.</a></p>';

	get_header();
	get_content();
	get_footer();

	exit;
}

?>