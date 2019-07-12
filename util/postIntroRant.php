<?php

// This file is used for setting up the bot
// It posts a rant that contains an introduction to the bot

require_once 'config.php';

require_once 'lib/Store.php';
require_once 'lib/DevRant.php';

$store = new Store('./store', ['prettify' => true, 'log' => DEBUG]);
$devRant = new DevRant($store);

// Post Rant
$rantText = file_get_contents('util/introRant.txt');
$rantID = $devRant->postRant($rantText, ['syntax', 'highlight', 'bot', 'tool', 'carbon', 'util', 'image', 'comment', 'code', 'script', 'carbon.now.sh', 'rant', 'feature']);