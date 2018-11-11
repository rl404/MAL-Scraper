<?php

require_once '../vendor/autoload.php';

header('Content-Type: application/json');

use MalScraper\MalScraper2;

ini_set('max_execution_time', 0);

//

$myscraper = new MalScraper2([

]);

$result = $myscraper->getInfo('anime', 84);

print_r($result);