<?php

require_once '../vendor/autoload.php';

header('Content-Type: application/json');

use MalScraper\MalScraper2;

ini_set('max_execution_time', 0);

//

$myscraper = new MalScraper2([
	// 'enable_cache' => true,
	// 'cache_time' => 2,
	// 'to_api' => true
]);

$result = $myscraper->getInfo('anime', 854);

print_r($result);