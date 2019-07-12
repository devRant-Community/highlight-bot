<?php

// This file is used for setting up the bot
// It posts a rant and many comments which are used to select the theme

require_once 'config.php';

require_once 'lib/Store.php';
require_once 'lib/DevRant.php';
require_once 'lib/ImageGenerator.php';

$store = new Store('./store', ['prettify' => true, 'log' => DEBUG]);
$devRant = new DevRant($store);
$imageGenerator = new ImageGenerator();

// Post Rant
$rantText = file_get_contents('util/themeSelectRant.txt');
$rantID = $devRant->postRant($rantText, ['syntax', 'highlight', 'bot', 'tool']);

// Post all comments
$themes = (require 'themes.php');
$previewCode = file_get_contents('util/previewCode.txt');
foreach ($themes as $theme) {
	ImageGenerator::generateAndSaveImage('./temp/' . $theme['id'] . '.png', $previewCode, $theme['id']);
	$devRant->postComment($rantID, $theme['name'], './temp/' . $theme['id'] . '.png');
}

// Store rant id
$configPHP = file_get_contents('config.php');
$configPHP = str_replace(
	'define(\'THEME_SELECTION_RANT_ID\', ' . THEME_SELECTION_RANT_ID . ');',
	'define(\'THEME_SELECTION_RANT_ID\', ' . $rantID . ');',
	$configPHP
);
file_put_contents('config.php', $configPHP);