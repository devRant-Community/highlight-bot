<?php

// This file gets executed every 5 seconds and handles everything

require_once '../config.php';

require_once '../lib/DevRant.php';

$devRant = new DevRant();



$rantText = file_get_contents('configRantText.txt');

var_dump($devRant->postRant($rantText, ['syntax', 'highlight', 'bot', 'tool']));
