<?php

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
			$page['content'] = get_contest_list();
			break;
		case (preg_match('/tehtavat\/(?<id>[0-9]+)/', $path, $matches) ? true : false) :
			get_task($matches['id']);
			$page['hide_title'] = true;
			break;
		case 'tehtavat':
			$page['title'] = 'Tehtävät';
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
	<html class="no-js" lang="fi">
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
			<title><?php echo $page['title']; ?></title>
			<meta name="description" content="">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			
			<meta name="robots" content="noindex, nofollow">

			<link rel="stylesheet" href="/assets/css/main.css" async>

			<?php /* <script src="/assets/js/vendor/modernizr-2.8.3.min.js"></script> */ ?>
		</head>
		<body>
			<a href="#" id="menu-toggle"><span></span><span></span><span></span></a>
			<header id="site-header">

				<a rel="home" href="/" title="Etusivu" class="site-title">Kisakanta</a>

				<nav>
					<ul>
						<li><a href="/kisat">Kilpailut</a></li>
						<li><a href="/tehtavat">Tehtävät</a></li>
						<li><a href="/tietoa">Tietoa</a></li>
					</ul>
				</nav>

				<nav class="secondary" role="secondary">
					<ul>
						<li><a href="/palaute">Palaute</a></li>
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
		<?php if(!isset($page['hide_title']) || !$page['hide_title']) : ?><h1><?php echo $page['title']; ?></h1><?php endif; ?>
		<?php echo $page['content']; ?>
	</article>
	<?php
}

function get_contest($slug) {
	global $db, $page;

	$query = $db->prepare('SELECT * FROM contests WHERE slug = ?');
	$query->execute(array($slug));

	if($query->rowCount() > 0) {
		$contest = $query->fetch();
		$page['title'] = $contest['name'];
		
		$page['content'] = '<p>';
		$page['content'] .= (empty($contest['year']) ? '' : '<strong>Ajankohta:</strong> ' . format_date($contest['year'], $contest['start_date'], $contest['end_date']));
		$page['content'] .= (empty($contest['location']) ? '' : '<br><strong>Kilpailualue:</strong> ' . $contest['location']);
		$page['content'] .= (empty($contest['theme']) ? '' : '<br><strong>Teema:</strong> ' . $contest['theme']);
		$page['content'] .= (empty($contest['organizer']) ? '' : '<br><strong>Järjestäjä:</strong> ' . (empty($contest['organizer_url']) ? $contest['organizer'] : '<a href="' . $contest['organizer_url'] . '" target="_blank">' . $contest['organizer'] . '</a>'));
		$page['content'] .= (empty($contest['contact']) ? '' : '<br><strong>Yhteyshenkilö:</strong> ' . $contest['contact']);
		$page['content'] .= '</p>';

		$page['content'] .= get_task_list_by_contest($contest['id']);

		$page['content'] .= (empty($contest['date_added']) ? '' : '<p class="meta">Lisätty ' . date('j.n.Y', strtotime($contest['date_added'])) . '</p>');

	} else {
		get_error(404);
	}
}

function get_task($id) {
	global $db, $page;

	$query = $db->prepare('SELECT contests.name AS contest_name, contests.slug AS contest_slug, contests.year, contests.start_date, contests.end_date, task_types.name AS type_name, categories.name AS category_name, tasks.* FROM tasks, contests, task_types, categories WHERE tasks.id = ? AND tasks.contest = contests.id AND task_types.id = tasks.task_type AND categories.id = tasks.category');
	$query->execute(array($id));

	if($query->rowCount() > 0) {
		$task = $query->fetch();
		$page['title'] = $task['name'];
		$page['content'] = '';
		
		$series = get_task_series($task['id']);
		$s = array();
		$p = array();

		foreach($series as $serie) {
			$s[] = $serie['name'];
			$p[] = $serie['max_points'];
		}

		$p3 = $p2 = $p;
		
		sort($p2, SORT_NUMERIC);
		rsort($p3, SORT_NUMERIC);

		if($p2 == $p3) {
			$max_points = 'Maksimipisteet: ' . $p[0];
		} else {
			$points = array();
			
			foreach($p as $k => $point) {
				$points[$point][] = $s[$k];
			}

			$max_points = 'Maksimipisteet:';
			$first = 1;
			
			foreach($points as $point => $serie) {
				$max_points .= strtolower(($first ? ' ' : ', ') . $point . ' (' . implode(', ', $serie) . ')');
				$first = 0;
			}
		}

		$page['content'] .= '<p><strong><a href="/kisat/' . $task['contest_slug'] . '">' . $task['contest_name'] . ' ' . format_date($task['year'], $task['start_date'], $task['end_date']) . '</a></strong>';
		$page['content'] .= '<br>' . ucfirst($task['category_name']);
		$page['content'] .= '<br>Sarjat: ' . implode(', ', $s);
		$page['content'] .= '<br>' . $max_points;
		$page['content'] .= '</p>';

		$page['content'] .= '<p><em>' . ucfirst($task['type_name']) . '</em></p>';

		$page['content'] .= '<h2>' . ucfirst($task['name']) . '</h2>';

		$page['content'] .= '<p>' . nl2br($task['task'], false) . '</p>';

		if(!empty($task['review'])) {
			$page['content'] .= '<h4>Arvostelu</h4>';
			$page['content'] .= '<p>' . nl2br($task['review'], false) . '</p>';
		}
		
		if(!empty($task['attachments'])) {
			$page['content'] .= '<h4>Liitteet</h4>';
			$page['content'] .= '<p>' . nl2br($task['attachments'], false) . '</p>';
		}

		$query = $db->prepare('SELECT id FROM tasks WHERE contest = ? AND name = ?');
		$query->execute(array($task['contest'], $task['name']));

		if($query->rowCount() > 1) {
			$page['content'] .= '<div class="alternative"><p>Tästä tehtävästä on olemassa erilliset versiot seuraaville sarjoille:</p><ul>';

			while($alt_task = $query->fetch()) {
				$alt_tasks[$alt_task['id']] = get_task_series($alt_task['id']);
			}

			foreach($alt_tasks as $alt_task => $alt_series) {
				$alt_series_looped = array();
				foreach($alt_series as $alt_serie) {
					$alt_series_looped[] = $alt_serie['name'];
				}
				if($alt_task == $task['id']) {
					$page['content'] .= '<li>' . implode(', ', $alt_series_looped) . '</li>';
				} else {
					$page['content'] .= '<li><a href="/tehtavat/' . $alt_task . '">' . implode(', ', $alt_series_looped) . '</a></li>';
				}
			}

			$page['content'] .= '</ul></div>';
		}

		$page['content'] .= (empty($task['date_added']) ? '' : '<p class="meta">Lisätty ' . date('j.n.Y', strtotime($task['date_added'])) . '</p>');

	} else {
		get_error(404);
	}
}

function get_contest_list() {
	global $db;

	$query = $db->prepare('SELECT contests.*, contest_types.name AS type FROM contests, contest_types WHERE contests.contest_type = contest_types.id ORDER BY contests.year DESC');
	$query->execute();

	if($query->rowCount() > 0) {

		while($contest = $query->fetch()) {
			$contests_by_type[$contest['type']][] = $contest;
		}

		$list = '';

		foreach($contests_by_type as $type => $contests) {
			$list .= '<h3>' . $type . '</h3>';
			$list .= '<ul class="contests">';	

			foreach($contests as $contest) {
				$list .= '<li><a href="/kisat/' . $contest['slug'] . '">' . $contest['name'] . '</a> <span class="meta">' . format_date($contest['year'], $contest['start_date'], $contest['end_date']) . ' ' . $contest['location'] . '</span></li>';
			}

			$list .= '</ul>';
		}

		return $list;

	} else {
		return '';
	}
}

function get_task_list() {
	global $db;

	$filters = '<h3>Hae tehtävistä</h3><form action="/tehtavat" method="get" class="filters">';
	$filters .= '<div class="form-group"><select name="kategoria">' . get_options('category') . '</select></div>';
	$filters .= '<div class="form-group"><select name="sarja">' . get_options('serie') . '</select></div>';
	$filters .= '<div class="form-group"><select name="tyyppi">' . get_options('type') . '</select></div>';
	$filters .= '<div class="form-group block"><input type="text" name="s" value="' . (!empty($_GET['s']) ? $_GET['s'] : '') . '" placeholder="Hae tehtävistä"></div>';
	$filters .= '<input type="submit" value="Hae"><input type="reset" value="Tyhjennä kentät">';
	$filters .= '</form>';

	$sql = 'SELECT * FROM tasks WHERE';
	$params = array();


	if(isset($_GET['sarja']) && !empty($_GET['sarja'])) {
		$sql = 'SELECT tasks.* FROM tasks, task_series, contest_series WHERE tasks.id = task_series.task AND contest_series.series = ? AND task_series.contest_series = contest_series.id';
		$params[] = $_GET['sarja'];
	}

	if(isset($_GET['kategoria']) && !empty($_GET['kategoria'])) {
		$sql .= ' AND tasks.category = ?';
		$params[] = $_GET['kategoria'];
	}
	
	if(isset($_GET['tyyppi']) && !empty($_GET['tyyppi'])) {
		$sql .= ' AND tasks.task_type = ?';
		$params[] = $_GET['tyyppi'];
	}
	
	if(isset($_GET['s']) && !empty($_GET['s'])) {
		$sql .= ' AND (tasks.name LIKE ? OR tasks.task LIKE ?)';
		$params[] = '%' . $_GET['s'] . '%';
		$params[] = '%' . $_GET['s'] . '%';
	}

	$sql .= ' GROUP BY tasks.name ORDER BY tasks.date_added DESC';
	$sql = str_replace('WHERE AND', 'WHERE', $sql);

	if(empty($params)) {
		$sql = 'SELECT * FROM tasks GROUP BY name ORDER BY date_added DESC LIMIT 20';
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
			$tasks .= '<li><a href="/tehtavat/' . $task['id'] . '">' . $task['name'] . '</a></li>';
		}

		$tasks .= '</ul>';

		return $filters . '<h3>' . $header . '</h3>' . $tasks;
	} else {
		return $filters . '<p>Ei hakutuloksia</p>';
	}
}


function get_task_list_by_contest($contest_id) {
	global $db;

	$query = $db->prepare('SELECT * FROM tasks WHERE contest = ? ORDER BY name ASC');
	$query->execute(array($contest_id));

	
	if($query->rowCount() > 0) {

		$all_series_in_contest = get_contest_series($contest_id);

		$query2 = $db->prepare('SELECT task_series.task, task_series.max_points, contest_series.series FROM task_series, contest_series WHERE contest_series.contest = ? AND task_series.contest_series = contest_series.id');
		$query2->execute(array($contest_id));

		$points_by_series = array();

		if($query2->rowCount() > 0) {
			while($row = $query2->fetch()) {
				$points_by_series[$row['task']][$row['series']] = $row['max_points'];
			}
		}
		
		$tasks = '<table><thead><tr>';
		$tasks .= '<th>Tehtävä</th>';

		foreach($all_series_in_contest as $serie) {
			$tasks .= '<th>' . ucfirst($serie['name']) . '</th>';
		}

		$tasks .= '</tr></thead><tbody>';

		while($task = $query->fetch()) {
			$_tasks[] = $task;
		}

		$count = count($_tasks);

		for($i = 0; $i < $count; $i++) {
			if($_tasks[$i]['name'] === $_tasks[$i + 1]['name']) {
				$points_by_series[$_tasks[$i + 1]['id']] = $points_by_series[$_tasks[$i]['id']] + $points_by_series[$_tasks[$i + 1]['id']];
				unset($_tasks[$i]);
			}
		}

		foreach($_tasks as $task) {
			$tasks .= '<tr><td><a href="/tehtavat/' . $task['id'] . '">' . $task['name'] . '</a></td>';

			foreach($all_series_in_contest as $serie) {
				if(isset($points_by_series[$task['id']][$serie['id']])) {
					$tasks .= '<td>' . $points_by_series[$task['id']][$serie['id']] . '</td>';
				} else {
					$tasks .= '<td></td>';
				}
			}
			$tasks .= '</tr>';
		}

		$tasks .= '</tbody></table>';

	} else {
		return '';
	}

	return $tasks;
}

function get_contest_series($contest_id) {
	global $db;

	$query = $db->prepare('SELECT series.name, series.short_name, series.id FROM series, contest_series WHERE contest_series.contest = ? AND contest_series.series = series.id ORDER BY series.`order`');
	$query->execute(array($contest_id));

	if($query->rowCount() > 0) {
		
		$series = array();

		while($serie = $query->fetch()) {
			$series[$serie['id']] = array('name' => $serie['name'], 'short_name' => $serie['short_name'], 'id' => $serie['id']);
		}
		
		return $series;

	} else {
		return array();
	}
}

function get_task_series($task_id) {
	global $db;

	$query = $db->prepare('SELECT series.name, task_series.max_points FROM series, task_series, contest_series WHERE task_series.task = ? AND contest_series.id = task_series.contest_series AND contest_series.series = series.id ORDER BY series.`order`');
	$query->execute(array($task_id));

	if($query->rowCount() > 0) {
		
		$tasks = array();

		while($task = $query->fetch()) {
			$tasks[] = array('name' => $task['name'], 'max_points' => $task['max_points']);
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

		$query = $db->prepare('SELECT * FROM categories ORDER BY name');
		$query->execute();

		$return .= '<option disabled' . (!isset($_GET['kategoria']) ? ' selected' : '') . '>Kategoria</option>';

		while($cat = $query->fetch()) {
			$return .= '<option value="' . $cat['id'] . '"' . ((isset($_GET['kategoria']) && $_GET['kategoria'] == $cat['id']) ? ' selected' : '') . '>' . $cat['name'] . '</option>';
		}

	} elseif($field == 'serie') {

		$query = $db->prepare('SELECT * FROM series ORDER BY name');
		$query->execute();

		$return .= '<option disabled' . (!isset($_GET['serie']) ? ' selected' : '') . '>Sarja</option>';

		while($cat = $query->fetch()) {
			$return .= '<option value="' . $cat['id'] . '"' . ((isset($_GET['sarja']) && $_GET['sarja'] == $cat['id']) ? ' selected' : '') . '>' . $cat['name'] . '</option>';
		}

	} elseif($field == 'type') {

		$query = $db->prepare('SELECT * FROM task_types ORDER BY name');
		$query->execute();

		$return .= '<option disabled' . (!isset($_GET['tyyppi']) ? ' selected' : '') . '>Tehtävätyyppi</option>';

		while($cat = $query->fetch()) {
			$return .= '<option value="' . $cat['id'] . '"' . ((isset($_GET['tyyppi']) && $_GET['tyyppi'] == $cat['id']) ? ' selected' : '') . '>' . $cat['name'] . '</option>';
		}

	}

	return $return;
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

function format_date($year, $start_date = '', $end_date = '') {
	return (empty($start_date) ? $year : (empty($end_date) ? date('j.n.Y', strtotime($start_date)) : date((date('Y', strtotime($start_date)) == date('Y', strtotime($end_date)) ? (date('m', strtotime($start_date)) == date('m', strtotime($end_date)) ? 'j.' : 'j.n.') : 'j.n.Y'), strtotime($start_date)) . '–' . date('j.n.Y', strtotime($end_date))));
}

?>