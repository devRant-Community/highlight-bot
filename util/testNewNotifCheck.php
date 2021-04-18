<?php

require_once 'config.php';
require_once 'lib/Store.php';
require_once 'lib/DevRant.php';

$store = new Store('./store', ['prettify' => true, 'log' => DEBUG]);
$devRant = new DevRant($store);

var_dump($devRant->getComment(2182804));