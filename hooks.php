<?php

/*
 * @package Simple Youtube Video Embedder/BBC
 * @version 2.0
 * @author LinuxPanda <linuxpanda@outlook.com>
 * @license Unlicense <http://unlicense.org>
 */

if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

$hooks = array(
	'integrate_pre_include' => '$sourcedir/SimpleYoutubeEmbed.php',
	'integrate_bbc_codes' => 'SYTE_BBC',
);

if (!empty($context['uninstalling']))
	$call = 'remove_integration_function';
else
	$call = 'add_integration_function';

foreach ($hooks as $hook => $function)
	$call($hook, $function);

?>
