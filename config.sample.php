<?php

// Fill the required information and rename this file config.php

$config['db']['host'] = '';
$config['db']['name'] = '';
$config['db']['user'] = '';
$config['db']['password'] = '';

$config['mailgun']['apikey'] = '';
$config['mailgun']['domain'] = '';

$config['google-analytics'] = '';

// Deployment configuration

define('SECRET_ACCESS_TOKEN', 'BetterChangeMeNowOrSufferTheConsequences');
define('REMOTE_REPOSITORY', 'https://github.com/villevuor/kisakanta.git');
define('BRANCH', 'master');
define('TARGET_DIR', '/');
define('DELETE_FILES', false);
define('EXCLUDE', serialize(array(
	'.git',
)));
define('TMP_DIR', '/tmp/spgd-'.md5(REMOTE_REPOSITORY).'/');
define('CLEAN_UP', false);
define('VERSION_FILE', TMP_DIR.'VERSION');
define('TIME_LIMIT', 30);
define('BACKUP_DIR', false);
define('USE_COMPOSER', true);
define('COMPOSER_OPTIONS', '--no-dev');
define('COMPOSER_HOME', false);
define('EMAIL_ON_ERROR', false);

?>
