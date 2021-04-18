<?php

require_once 'config.php';
require_once 'lib/ImageGenerator.php';

ImageGenerator::generateAndSaveImage('test.png', '%ms-action-grid-fix {
-ms-grid-column-span: 999
-ms-grid-column-align: stretch;
}','monokai');