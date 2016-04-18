<?php


function get_header() {
	?><!doctype html>
	<html class="no-js" lang="fi">
		<head>
			<meta charset="utf-8">
			<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
			<title><?php get_title(); ?></title>
			<meta name="description" content="">
			<meta name="viewport" content="width=device-width, initial-scale=1">

			<link rel="stylesheet" href="assets/css/normalize.min.css">
			<link rel="stylesheet" href="assets/css/main.css">

			<script src="assets/js/vendor/modernizr-2.8.3.min.js"></script>
		</head>
		<body>
	<?php
}

function get_footer() {
	?>

			<script src="assets/js/main.js"></script>

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

function the_title() {
	echo 'Kisakanta';
}

?>