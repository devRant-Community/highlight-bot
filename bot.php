<?php

// This file gets executed every 5 seconds and handles everything

require_once 'config.php';

require_once 'lib/DevRant.php';
require_once 'lib/ImageGenerator.php';
require_once 'lib/UserConfig.php';

$devRant = new DevRant();

echo json_encode($devRant->getRant(2161294), JSON_PRETTY_PRINT);