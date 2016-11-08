<?php

require('vendor/autoload.php');
use Mailgun\Mailgun;

$page = array();

try {
	$db = new PDO('mysql:host=' . $config['db']['host'] . ';dbname=' . $config['db']['name'], $config['db']['user'], $config['db']['password']);
} catch (PDOException $error) {
	get_error(500, 'Virhe tietokantayhteydessä.');
}

$db->exec("SET NAMES utf8mb4");

function page_setup() {
	global $page;

	$path = (!isset($_GET['path']) || empty($_GET['path']) ? '' : $_GET['path']);

	// Remove first slash from path
	$path = substr($path, 1);

	switch ($path) {
		case '':
			$page['title'] = 'Kisakanta';
			$page['hide_title'] = true;
			$page['meta_description'] = 'Kisakanta on arkisto partiotaitokisojen tehtäväkäskyille. Selaa vanhoja kisoja, hae ideoita rastitehtäviin ja lähetä oma materiaalisi!';
			$page['content'] = get_front_page();
			break;
		case 'tietoa':
			$page['title'] = 'Tietoa';
			$page['meta_description'] = 'Tietoa Kisakanta-palvelusta ja sen historiasta';
			$page['content'] = '<p>Kisakanta on Espoon Partiotuen pystyttämä ja ylläpitämä pankki partiotaitokisojen tehtäväkäskyille. Kiskan tarkoituksena on säilöä kootusti kisoihin tuotettua materiaalia ja tarjota vinkkejä uusia kisoja suunnitteleville. Palvelu on tarkoitettu kaikille partiotaitokilpailuille ympäri Suomen, eikä pelkästään EPT:n omille kisoille.</p>';
			$page['content'] .= '<p>Kisakanta on perustettu vuonna 2010. Vuoden 2016 alussa sivusto joutui hakkeroinnin kohteeksi, mutta materiaali saatiin pelastettua ja palvelu palasi entistä ehompana marraskuussa 2016.</p>';
			$page['content'] .= '<h3>Materiaali on tervetullutta</h3><p>Lähetä järjestämäsi kilpailun tehtäväkäskyt ja muu haluamasi materiaali meille! Arkistointi pitää kilpailun tärkeimmän sisällön eli tehtäväkäskyt tallessa, ja helpottaa muita partiokisoja järjestäviä antamalla inspiraatiota ja vinkkejä. Samalla ylläpidät osaltasi suomalaista partiotaitokilpailukulttuuria.</p><p>Aineiston lähetys on tehty mahdollisimman helpoksi. <a href="/laheta">Katso tarkemmat ohjeet ja lähetä materiaalia täällä.</a></p>';
			$page['content'] .= '<h3>Yhteystiedot</h3><p>Voit ottaa yhteyttä Kisakantaan liittyvissä asioissa <a href="/palaute">palautelomakkeella</a>.</p>';
			break;
		case 'laheta':
			$page['title'] = 'Lähetä kilpailusi';
			$page['meta_description'] = 'Osallistu arkistointiin ja lähetä järjestämäsi kilpailun aineistot kisakantaan!';
			$page['content'] = get_contest_form();
			break;
		case 'palaute':
			$page['title'] = 'Palaute';
			$page['meta_description'] = 'Anna palautetta Kisakanta-palvelusta';
			$page['content'] = get_feedback_form();
			break;
		case (preg_match('/kisat\/(?<slug>[\-a-zA-Z0-9]+)/', $path, $matches) ? true : false) :
			get_contest($matches['slug']);
			break;
		case 'kisat':
			$page['title'] = 'Kilpailut';
			$page['meta_description'] = 'Selaa Kisakanta-palveluun tallenettuja kilpailuita.';
			$page['content'] = get_contest_list();
			break;
		case (preg_match('/tehtavat\/(?<id>[0-9]+)/', $path, $matches) ? true : false) :
			get_task($matches['id']);
			$page['hide_title'] = true;
			break;
		case 'tehtavat':
			$page['title'] = 'Tehtävät';
			$page['meta_description'] = 'Selaa Kisakanta-palveluun tallenettuja tehtäviä.';
			$page['content'] = get_task_list();
			break;
		default:
			get_error(404);
			break;
	}
}

function get_header() {
	global $page;
	?><!doctype html>
	<html class="no-js" lang="fi" prefix="og: http://ogp.me/ns#">
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
			<title><?php echo $page['title']; ?></title>

			<meta name="description" content="<?php echo $page['meta_description']; ?>">
			<?php if(!empty($page['date'])) : ?><meta name="pubdate" content="<?php echo $page['date']; ?>"><?php endif; ?>

			<meta name="viewport" content="width=device-width, initial-scale=1">
			<meta name="robots" content="noindex, nofollow">

			<link rel="stylesheet" href="/assets/css/main.css" async>

			<link rel="apple-touch-icon" sizes="180x180" href="/assets/favicon/apple-touch-icon.png">
			<link rel="icon" type="image/png" href="/assets/favicon/favicon-32x32.png" sizes="32x32">
			<link rel="icon" type="image/png" href="/assets/favicon/favicon-16x16.png" sizes="16x16">
			<link rel="manifest" href="/assets/favicon/manifest.json">
			<link rel="mask-icon" href="/assets/favicon/safari-pinned-tab.svg" color="#009688">
			<link rel="shortcut icon" href="/assets/favicon/favicon.ico">
			<meta name="msapplication-config" content="/assets/favicon/browserconfig.xml">
			<meta name="theme-color" content="#ffffff">

			<meta property="og:type" content="article">
			<meta property="og:title" content="<?php echo $page['title']; ?>">
			<meta property="og:description" content="<?php echo $page['meta_description']; ?>">
			<meta property="og:url" content="<?php echo 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
			<meta property="og:image" content="/assets/img/_MG_5302_1200x630_colored.jpg">
			<meta property="og:locale" content="fi_FI">
			<meta property="og:site_name" content="Kisakanta">
		</head>
		<body>
			<a href="#" id="menu-toggle"><span></span><span></span><span></span></a>
			<header id="site-header">

				<a rel="home" href="/" title="Etusivu" class="site-title">Kisakanta</a>

				<nav>
					<ul>
						<li><a href="/kisat">Kilpailut</a></li>
						<li><a href="/tehtavat">Tehtävät</a></li>
						<li><a href="/laheta">Lähetä kilpailusi</a></li>
						<li><a href="/tietoa">Tietoa</a></li>
						<li><a href="/palaute">Palaute</a></li>
					</ul>
				</nav>

				<a href="http://ept.fi" target="_blank" id="ept"><img src="/assets/img/EPT_logo.png" alt="Espoon Partiotuki ry"></a>

			</header>

			<main id="content" role="main">
	<?php
}

function get_footer() {
	global $config;
	?>
			</main>

			<script src="/assets/js/main.js"></script>

			<script>
				(function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;e=o.createElement(i);r=o.getElementsByTagName(i)[0];e.src='//www.google-analytics.com/analytics.js';r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));

				ga('create', '<?php echo $config['google-analytics']; ?>','auto');
				ga('send', 'pageview');
			</script>
		</body>
	</html>
	<?php
}

function get_content() {
	global $page;
	?>
	<article>
		<?php if(!isset($page['hide_title']) || !$page['hide_title']) : ?><h1><?php echo $page['title']; ?></h1><?php endif; ?>
		<?php echo $page['content']; ?>
	</article>
	<?php
}

function get_contest($slug) {
	global $db, $page;

	$query = $db->prepare('SELECT contests.*, files.location AS logo FROM contests LEFT JOIN files ON (contests.logo IS NOT NULL AND contests.logo = files.id) WHERE slug = ?');
	$query->execute(array($slug));

	if($query->rowCount() > 0) {
		$contest = $query->fetch();
		$page['title'] = $contest['title'];

		$page['content'] = (isset($contest['logo']) ? '<img src="' . $contest['logo'] . '" alt="Kilpailun logo" class="logo">' : '');

		$page['content'] .= '<p>';
		$page['content'] .= (empty($contest['year']) ? '' : '<strong>Ajankohta:</strong> ' . format_date($contest['year'], $contest['start_date'], $contest['end_date']));
		$page['content'] .= (empty($contest['location']) ? '' : '<br><strong>Kilpailualue:</strong> ' . $contest['location']);
		$page['content'] .= (empty($contest['theme']) ? '' : '<br><strong>Teema:</strong> ' . $contest['theme']);
		$page['content'] .= (empty($contest['organizer']) ? '' : '<br><strong>Järjestäjä:</strong> ' . (empty($contest['organizer_url']) ? $contest['organizer'] : '<a href="' . $contest['organizer_url'] . '" target="_blank">' . $contest['organizer'] . '</a>'));
		$page['content'] .= (empty($contest['contact']) ? '' : '<br><strong>Yhteyshenkilö:</strong> ' . $contest['contact']);
		$page['content'] .= '</p>';

		if ( $contest['is_punkku'] ) {
			$page['content'] .= punkku_series_notice();
		}

		$page['content'] .= get_task_list_by_contest($contest['id']);

		$query = $db->prepare('SELECT files.location, files.title FROM files, contest_files WHERE contest_files.contest = ? AND contest_files.file = files.id');
		$query->execute(array($contest['id']));

		if($query->rowCount() > 0) {
			$page['content'] .= '<h3>Tiedostot</h3><ul>';

			while($attachment = $query->fetch()) {
				$page['content'] .= '<li><a href="' . $attachment['location'] . '">' . ucfirst($attachment['title']) . '</a></li>';
			}

			$page['content'] .= '</ul>';
		}

		$page['content'] .= (empty($contest['date_added']) ? '' : '<p class="meta">Lisätty ' . date('j.n.Y', strtotime($contest['date_added'])) . '</p>');

		$page['meta_description'] = $contest['title'] . ' järjestettiin ' . format_date($contest['year'], $contest['start_date'], $contest['end_date']) . '. Kilpailun tehtäväkäskyt on tallennettu Kisakantaan.';
		$page['date'] = date('Y-m-d', strtotime($contest['date_added']));

	} else {
		get_error(404);
	}
}

function get_task($id) {
	global $db, $page;

	$query = $db->prepare('SELECT tasks.*, task_versions.*, contests.title AS contest_title, contests.slug AS contest_slug, contests.year, contests.start_date, contests.end_date, task_version_types.title AS type, task_categories.name AS category, tasks.id AS task_id FROM tasks, task_versions, contests, task_version_types, task_categories WHERE task_versions.id = ? AND task_versions.task_id = tasks.id AND tasks.contest = contests.id AND task_version_types.id = task_versions.type AND task_categories.id = tasks.category');
	$query->execute(array($id));

	if($query->rowCount() > 0) {
		$task = $query->fetch();

		$series = get_task_version_series($task['id']);

		$page['title'] = $task['title'];
		$page['meta_description'] = truncate($task['content']);
		$page['date'] = date('Y-m-d', strtotime($task['date_added']));
		$page['content'] = '';

		$page['content'] .= '<p><strong><a href="/kisat/' . $task['contest_slug'] . '">' . $task['contest_title'] . ' ' . format_date($task['year'], $task['start_date'], $task['end_date']) . '</a></strong>';
		$page['content'] .= '<br>' . $task['category'];
		$page['content'] .= '<br>Sarjat: ' . implode(', ', $series);
		$page['content'] .= (!empty($task['max_points']) ? '<br>Maksimipisteet: ' . $task['max_points'] : '');
		$page['content'] .= '</p>';

		$page['content'] .= '<p><em>' . ucfirst($task['type']) . '</em></p>';

		$page['content'] .= '<h2>' . ucfirst($task['title']) . '</h2>';

		$page['content'] .= '<p>' . nl2br($task['content'], false) . '</p>';

		if(!empty($task['review'])) {
			$page['content'] .= '<h4>Arvostelu</h4>';
			$page['content'] .= '<p>' . nl2br($task['review'], false) . '</p>';
		}

		if(!empty($task['attachments'])) {
			$page['content'] .= '<h4>Liitteet</h4>';
			$page['content'] .= '<p>' . nl2br($task['attachments'], false) . '</p>';
		}

		$query = $db->prepare('SELECT files.* FROM files, task_version_files WHERE task_version_files.task_version = ? AND task_version_files.file = files.id');
		$query->execute(array($id));

		if($query->rowCount() > 0) {
			$page['content'] .= '<h4>Tiedostot</h4><ul>';

			while($file = $query->fetch()) {
				$page['content'] .= '<li><a href="' . $file['location'] . '">' . ucfirst($file['title']) . '</a></li>';
			}

			$page['content'] .= '</ul>';
		}

		$query = $db->prepare('SELECT id FROM task_versions WHERE task_id = ?');
		$query->execute(array($task['task_id']));

		if($query->rowCount() > 1) {
			$page['content'] .= '<div class="alternative"><p>Tästä tehtävästä on olemassa erilliset versiot seuraaville sarjoille:</p><ul>';

			while($alt_task = $query->fetch()) {
				$alt_tasks[$alt_task['id']] = get_task_version_series($alt_task['id'], true);
			}

			foreach($alt_tasks as $alt_task => $alt_series) {
				if($alt_task == $task['id']) {
					$page['content'] .= '<li>' . implode(', ', $alt_series) . '</li>';
				} else {
					$page['content'] .= '<li><a href="/tehtavat/' . $alt_task . '">' . implode(', ', $alt_series) . '</a></li>';
				}
			}

			$page['content'] .= '</ul></div>';
		}

		$page['content'] .= (empty($task['date_added']) ? '' : '<p class="meta">Lisätty ' . date('j.n.Y', strtotime($task['date_added'])) . '</p>');

		// Update views count
		$query = $db->prepare('UPDATE task_versions SET views = views + 1 WHERE id = ?');
		$query->execute(array($id));

	} else {
		get_error(404);
	}
}

function get_contest_list() {
	global $db;

	$query = $db->prepare('SELECT * FROM contests ORDER BY contests.year DESC');
	$query->execute();

	if($query->rowCount() > 0) {

		while($contest = $query->fetch()) {
			$contests[$contest['category']][] = $contest;
		}

		$list = '';

		foreach(get_contest_categories() as $category => $subcategories) {
			$list .= '<h2>' . $category . '</h2>';

			foreach($subcategories as $title => $id) {
				$list .= '<h4>' . $title . '</h4>';
				$list .= '<ul class="contests">';
				foreach($contests[$id] as $contest) {
					$list .= '<li><a href="/kisat/' . $contest['slug'] . '">' . $contest['title'] . '</a> <span class="meta">' . format_date($contest['year'], $contest['start_date'], $contest['end_date']) . ' ' . $contest['location'] . '</span></li>';
				}
				$list .= '</ul>';
			}
		}

		return $list;

	} else {
		return '';
	}
}

function get_contest_categories() {
	global $db;

	$query = $db->prepare('SELECT * FROM contest_categories ORDER BY `order` ASC');
	$query->execute();

	if($query->rowCount() > 0) {
		$cats = $query->fetchAll();

		$parent = $return = array();

		foreach($cats as $cat) {
			if(empty($cat['parent'])) {
				$parent[$cat['id']] = $cat['title'];
			}
		}

		foreach($cats as $cat) {
			if(!empty($cat['parent'])) {
				$return[$parent[$cat['parent']]][$cat['title']] = $cat['id'];
			}
		}

		return $return;
	}

	return array();
}

function get_task_list() {
	global $db;

	$filters = '<h3>Hae tehtävistä</h3><form action="/tehtavat" method="get" class="filters">';
	$filters .= '<div class="form-group"><select name="kategoria">' . get_options('category') . '</select></div>';
	$filters .= '<div class="form-group"><select name="sarja">' . get_options('serie') . '</select></div>';
	$filters .= '<div class="form-group"><select name="kisa">' . get_options('contest') . '</select></div>';
	$filters .= '<div class="form-group block"><input type="text" name="s" value="' . (!empty($_GET['s']) ? $_GET['s'] : '') . '" placeholder="Hae tehtävistä"></div>';
	$filters .= '<input type="submit" value="Hae"><input type="reset" value="Tyhjennä kentät">';
	$filters .= '</form>';

	$sql = 'SELECT task_versions.id, tasks.title FROM tasks, task_versions WHERE tasks.id = task_versions.task_id';
	$params = array();

	if(isset($_GET['sarja']) && !empty($_GET['sarja'])) {
		$sql = 'SELECT task_versions.id, tasks.title FROM tasks, task_versions, task_version_series WHERE tasks.id = task_versions.task_id AND task_versions.id = task_version_series.task_version AND task_version_series.series = ?';
		$params[] = $_GET['sarja'];
	}

	if(isset($_GET['kategoria']) && !empty($_GET['kategoria'])) {
		$sql .= ' AND tasks.category = ?';
		$params[] = $_GET['kategoria'];
	}

	if(isset($_GET['kisa']) && !empty($_GET['kisa'])) {
		$sql .= ' AND tasks.contest = ?';
		$params[] = $_GET['kisa'];
	}

	if(isset($_GET['s']) && !empty($_GET['s'])) {
		$sql .= ' AND (tasks.name LIKE ? OR task_versions.content LIKE ? OR task_versions.tags LIKE ?)';
		$params[] = '%' . $_GET['s'] . '%';
		$params[] = '%' . $_GET['s'] . '%';
		$params[] = '%' . $_GET['s'] . '%';
	}

	$sql .= ' GROUP BY task_versions.task_id ORDER BY tasks.title ASC';
	$sql = str_replace('WHERE AND', 'WHERE', $sql);

	if(empty($params)) {
		$sql = 'SELECT tasks.title, task_versions.id, task_versions.date_added FROM tasks, task_versions WHERE tasks.id = task_versions.task_id ORDER BY task_versions.date_added DESC LIMIT 20';
		$header = 'Viimeksi lisätyt tehtävät';
	}

	$query = $db->prepare($sql);
	$query->execute($params);

	if($query->rowCount() > 0) {

		if(empty($header)) {
			$count = $query->rowCount();
			$header = ($count == 1 ? '1 hakutulos' : $count . ' hakutulosta');
		}

		$tasks = '<ul>';

		while($task = $query->fetch()) {
			$tasks .= '<li><a href="/tehtavat/' . $task['id'] . '">' . $task['title'] . '</a></li>';
		}

		$tasks .= '</ul>';

		return $filters . '<h3>' . $header . '</h3>' . $tasks;
	} else {
		return $filters . '<p>Ei hakutuloksia</p>';
	}
}


function get_task_list_by_contest($contest_id) {
	global $db;

	$query = $db->prepare('SELECT tasks.id, tasks.title, task_versions.id AS task_version_id, task_versions.max_points, series.id AS series, series.short_title AS series_short, series.`order` FROM tasks, task_versions, task_version_series, series WHERE tasks.contest = ? AND task_versions.task_id = tasks.id AND task_version_series.task_version = task_versions.id AND task_version_series.series = series.id ORDER BY tasks.title ASC, series.`order` ASC');
	$query->execute(array($contest_id));


	if($query->rowCount() > 0) {

		while($task = $query->fetch()) {
			$_tasks[$task['id']][] = $task;
		}

		$all_series_in_contest = get_contest_series($contest_id);

		$tasks = '<table><thead><tr>';
		$tasks .= '<th>Tehtävä</th>';

		$series = get_contest_series($contest_id);

		foreach($series as $serie) {
			$tasks .= '<th>' . ucfirst($serie['title']) . '</th>';
			$task_versions_empty[$serie['id']] = '<td></td>';
		}

		$tasks .= '</tr></thead><tbody>';

		foreach($_tasks as $task) {

			$task_versions = array();
			$task_version_links = '';
			$task_version_points = $task_versions_empty;

			foreach($task as $task_version) {
				$task_versions[$task_version['task_version_id']] = (!isset($task_versions[$task_version['task_version_id']]) ? $task_version['series_short'] : $task_versions[$task_version['task_version_id']] . $task_version['series_short']);
				$task_version_points[$task_version['series']] = '<td>' . (empty($task_version['max_points']) ? '–' : $task_version['max_points']) . '</td>';
			}

			if(count($task_versions) > 1) {
				foreach ($task_versions as $id => $series) {
					$task_version_links .= ' / <a href="/tehtavat/' . $id . '">' . $series . '</a>';
				}
				$task_version_links = ' (' . substr($task_version_links, 3) . ')';
			}


			$tasks .= '<tr><td><a href="/tehtavat/' . $task[0]['task_version_id'] . '">' . $task[0]['title'] . '</a>' . $task_version_links . '</td>' . implode($task_version_points) . '</tr>';
		}

		$tasks .= '</tbody></table>';

	} else {
		return '';
	}

	return $tasks;
}

function get_contest_series($contest_id) {
	global $db;

	$query = $db->prepare('SELECT DISTINCT series.title, series.short_title, series.id, series.`order` FROM series, tasks, task_versions, task_version_series WHERE tasks.contest = ? AND tasks.id = task_versions.task_id AND task_versions.id = task_version_series.task_version AND task_version_series.series = series.id ORDER BY series.`order`');
	$query->execute(array($contest_id));

	if($query->rowCount() > 0) {
		return $query->fetchAll();
	} else {
		return array();
	}
}

function get_task_version_series($task_version_id, $long = false) {
	global $db;

	$query = $db->prepare('SELECT series.short_title, series.title, series.`order` FROM series, task_version_series WHERE task_version_series.task_version = ? AND task_version_series.series = series.id ORDER BY series.`order`');
	$query->execute(array($task_version_id));

	if($query->rowCount() > 0) {

		$tasks = array();

		while($task = $query->fetch()) {
			$tasks[] = ($long ? $task['title'] : $task['short_title']);
		}

		return $tasks;

	} else {
		return array();
	}
}

function get_options($field) {
	global $db;

	$return = '';

	if($field == 'category') {

		$query = $db->prepare('SELECT * FROM task_categories ORDER BY name');
		$query->execute();

		$return .= '<option disabled' . (!isset($_GET['kategoria']) ? ' selected' : '') . '>Kategoria</option>';

		while($cat = $query->fetch()) {
			$return .= '<option value="' . $cat['id'] . '"' . ((isset($_GET['kategoria']) && $_GET['kategoria'] == $cat['id']) ? ' selected' : '') . '>' . $cat['name'] . '</option>';
		}

	} elseif($field == 'serie') {

		$query = $db->prepare('SELECT * FROM series ORDER BY title');
		$query->execute();

		$return .= '<option disabled' . (!isset($_GET['serie']) ? ' selected' : '') . '>Sarja</option>';

		while($cat = $query->fetch()) {
			$return .= '<option value="' . $cat['id'] . '"' . ((isset($_GET['sarja']) && $_GET['sarja'] == $cat['id']) ? ' selected' : '') . '>' . $cat['title'] . '</option>';
		}

	} elseif($field == 'contest') {

		$query = $db->prepare('SELECT id, title FROM contests ORDER BY title');
		$query->execute();

		$return .= '<option disabled' . (!isset($_GET['kisa']) ? ' selected' : '') . '>Kilpailu</option>';

		while($cat = $query->fetch()) {
			$return .= '<option value="' . $cat['id'] . '"' . ((isset($_GET['kisa']) && $_GET['kisa'] == $cat['id']) ? ' selected' : '') . '>' . $cat['title'] . '</option>';
		}

	}

	return $return;
}


function get_front_page() {
	global $db;

	$front_page = '<img src="/assets/img/_MG_5302.jpg" alt="Vuosikerta-Punkun lähtö 20.9.2014">';
	$front_page .= '<h1>Tervetuloa Kisakantaan</h1>';

	$front_page .= '<p>Kisakanta on arkisto <a href="https://fi.scoutwiki.org/Partiotaitokilpailut" target="_blank">partiotaitokilpailujen</a> tehtäväkäskyille ja muille dokumenteille. Palvelussa voit fiilistellä pt-kisoja jälkikäteen tai etsiä esimerkiksi valmiita tehtäviä lippukuntakisoihin.</p>';

	$query = $db->prepare('SELECT COUNT(*) as count FROM contests');
	$query->execute();
	$contest_count = $query->fetch()['count'];

	$query = $db->prepare('SELECT COUNT(*) as count FROM tasks');
	$query->execute();
	$task_count = $query->fetch()['count'];

	$front_page .= '<p>Kisakannasta löytyy tällä hetkellä <a href="/kisat">' . $contest_count . ' kilpailua</a> ja <a href="/tehtavat">' . $task_count . ' tehtävää</a>. Osallistu Kisakannan arkistointiin <a href="laheta">lähettämällä oma kilpailusi palveluun</a>!';

	$query = $db->prepare('SELECT title, slug, date_added FROM contests ORDER BY date_added DESC LIMIT 5');
	$query->execute();

	$front_page .= '<div class="column"><h3>Äskettäin lisätyt kilpailut</h3><ul>';

	while($contest = $query->fetch()){
		$front_page .= '<li><a href="/kisat/' . $contest['slug'] . '">' . $contest['title'] . '</a><span class="meta">, lisätty ' . date('j.n.Y', strtotime($contest['date_added'])) . '</span></li>';
	}

	$front_page .= '</ul></div>';


	$query = $db->prepare('SELECT tasks.title, task_versions.id, task_versions.views FROM task_versions, tasks WHERE tasks.id = task_versions.task_id ORDER BY task_versions.views DESC LIMIT 5');
	$query->execute();

	$front_page .= '<div class="column"><h3>Eniten katsotut tehtävät</h3><ol>';

	while($task = $query->fetch()){
		$front_page .= '<li><a href="/tehtavat/' . $task['id'] . '">' . $task['title'] . '</a></li>';
	}

	$front_page .= '</ol></div>';

	$front_page .= '<p class="meta">Kuva: Tero Honkaniemi, Espoon Punanen 2014</p>';

	return $front_page;
}

function get_contest_form() {
	global $db, $config;

	$helper = '<h3>Tehtäväkäskyjen ja muun materiaalin toimitus</h3>';
	$helper .= '<p>Toimita tehtäväkäskyt, tehtäväluettelo (esim. kisakutsun osana) ja muut materiaalit <a href="https://www.dropbox.com/request/IjBGZsoG8EbViqeLjzi5" target="_blank">Dropbox-kansioomme</a>.</p>';
	$helper .= '<p>Toimitathan materaalin mahdollisimman selkeässä muodossa (esim. ei työversioita).</p>';
	$helper .= '<p>Voit liittää mukaan myös muita tiedostoja, kuten kilpailukutsun, arvostelupöytäkirjoja tai kilpailun logon. Vaiva ei nyt ole suuri, mutta 10 vuoden kuluttua on kiva tarkastella kisoja, joissa on enemmän materiaalia. :)</p>';
	$helper .= '<p>Suurkiitos kaikille materiaalia toimittaville!</p>';

	if(isset($_POST['name']) && !empty(trim($_POST['name']))) {

		$feedback = 'Kilpailun nimi: ' . $_POST['name'] . PHP_EOL;
		$feedback .= 'Kilpailun kotisivut: ' . $_POST['homepage'] . PHP_EOL;
		$feedback .= 'Ajankohta: ' . $_POST['date'] . PHP_EOL;
		$feedback .= 'Kilpailualue: ' . $_POST['area'] . PHP_EOL;
		$feedback .= 'Kilpailun teema/aihe: ' . $_POST['theme'] . PHP_EOL;
		$feedback .= 'Järjestäjä (organisaatio): ' . $_POST['organizer'] . PHP_EOL;
		$feedback .= 'Järjestäjän kotisivut: ' . $_POST['organizer_url'] . PHP_EOL;
		$feedback .= 'Yhteyshenkilö: ' . $_POST['person'] . PHP_EOL;
		$feedback .= 'Lähettäjän sähköposti: ' . $_POST['email'] . PHP_EOL;

		$query = $db->prepare('INSERT INTO feedbacks (feedback, name, email, ip) VALUES (?, ?, ?, ?)');
		$query->execute(array($feedback, (empty($_POST['person']) ? null : $_POST['person']), (empty($_POST['email']) ? null : $_POST['email']), $_SERVER['REMOTE_ADDR']));

		$client = new \Http\Adapter\Guzzle6\Client();

		$mailgun = new Mailgun($config['mailgun']['apikey'], $client);

		$mailgun->sendMessage($config['mailgun']['domain'],
			array(
				'from'    => 'noreply@kisakanta.fi',
				'to'      => 'villevuor@gmail.com',
				'subject' => 'Uusi kilpailu Kisakantaan: ' . $_POST['name'],
				'text'    => $feedback,
			)
		);

		$form = '<p>Kiitos viestistäsi! Muistathan vielä lähettää tehtäväkäskyt ja muut haluamasi materiaalit alla olevien ohjeiden mukaan.</p>';
		$form .= $helper;

	} else {

		$form = '<p>Voit lähettää kilpailusi palveluun tällä lomakkeella. Täytä ensin kilpailun perustiedot, ja lue sen jälkeen ohjeet tehtäväkäskyjen lähetyksestä.</p>';
		$form .= '<form action="/laheta" method="post" class="feedback">';
		$form .= '<input type="text" placeholder="Kilpailun nimi" name="name" required>';
		$form .= '<input type="text" placeholder="Kilpailun kotisivut" name="homepage">';
		$form .= '<input type="text" placeholder="Ajankohta" name="date" required>';
		$form .= '<input type="text" placeholder="Kilpailualue" name="area" required>';
		$form .= '<input type="text" placeholder="Kilpailun teema/aihe" name="theme">';
		$form .= '<input type="text" placeholder="Järjestäjä (organisaatio)" name="organizer" required>';
		$form .= '<input type="text" placeholder="Järjestäjän kotisivut" name="organizer_url">';
		$form .= '<input type="text" placeholder="Yhteyshenkilö" name="person" required>';
		$form .= '<input type="email" placeholder="Lähettäjän sähköposti (ei julkaista)" name="email" required>';
		$form .= '<input type="submit" value="Lähetä">';
		$form .= '</form>';

		$form .= $helper;

	}

	return $form;
}

function get_feedback_form() {
	global $db, $config;

	if(isset($_POST['feedback']) && !empty(trim($_POST['feedback']))) {

		$query = $db->prepare('INSERT INTO feedbacks (feedback, name, email, ip) VALUES (?, ?, ?, ?)');

		$query->execute(array($_POST['feedback'], (empty($_POST['name']) ? null : $_POST['name']), (empty($_POST['email']) ? null : $_POST['email']), $_SERVER['REMOTE_ADDR']));

		$client = new \Http\Adapter\Guzzle6\Client();

		$mailgun = new Mailgun($config['mailgun']['apikey'], $client);

		$mailgun->sendMessage($config['mailgun']['domain'],
			array(
				'from'    => 'noreply@kisakanta.fi',
				'to'      => 'villevuor@gmail.com',
				'subject' => 'Palautetta Kisakannasta ' . date('j.n.Y'),
				'text'    => 'Lähettäjä: ' . (empty($_POST['name']) ? '–' : $_POST['name']) . PHP_EOL . 'Sähköposti: ' . (empty($_POST['email']) ? '–' : $_POST['email']) . PHP_EOL . PHP_EOL . $_POST['feedback'],
			)
		);

		$form = '<p>Kiitos palautteestasi!</p>';

	} else {

		$form = '<p>Voit antaa palvelusta palautetta tällä lomakkeella. Täytähän yhteystietosi, jos haluat viestillesi vastauksen.</p>';
		$form .= '<form action="/palaute" method="post" class="feedback">';
		$form .= '<textarea name="feedback" placeholder="Palautteesi" required></textarea>';
		$form .= '<input type="text" placeholder="Nimi" name="name" maxlength="50">';
		$form .= '<input type="email" placeholder="Sähköposti" name="email" maxlength="50">';
		$form .= '<input type="submit" value="Lähetä">';
		$form .= '</form>';

	}

	return $form;
}

function get_error($code = 404, $msg = 'Sivua ei löytynyt.') {
	global $page;

	header('Error', true, $code);

	$page['title'] = 'Virhe ' . $code;
	$page['meta_description'] = $msg;
	$page['content'] = '<p>' . $msg . '</p><p><a href="/">Palaa etusivulle.</a></p>';

	get_header();
	get_content();
	get_footer();

	exit;
}

function format_date($year, $start_date = '', $end_date = '') {
	return (empty($start_date) ? $year : (empty($end_date) ? date('j.n.Y', strtotime($start_date)) : date((date('Y', strtotime($start_date)) == date('Y', strtotime($end_date)) ? (date('m', strtotime($start_date)) == date('m', strtotime($end_date)) ? 'j.' : 'j.n.') : 'j.n.Y'), strtotime($start_date)) . '–' . date('j.n.Y', strtotime($end_date))));
}

function punkku_series_notice() {
	return '<div class="alternative punkku"><p>Espoon Punasessa tyttöjen sarjojen nimet ovat erilaiset kuin valtakunnallisissa kisoissa. 14-18-vuotiaiden tyttöjen sarja on <em>keltainen</em> ja yli 18-vuotiaiden naisten sarja taas <em>sininen</em>.</p></div>';
}

function truncate($string, $length = 160, $append = "…") {
	$string = trim($string);

	if(strlen($string) > $length) {
		$string = wordwrap($string, $length);
		$string = explode("\n", $string, 2);
		$string = trim($string[0]);
		$string = $string . (in_array(substr($string, -1), array('!', '?', '.')) ? '' : $append);
	}

	return $string;
}

?>
