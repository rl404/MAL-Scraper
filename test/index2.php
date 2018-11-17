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

// $result = $myscraper->getInfo('anime', 2123);
// $result = $myscraper->getCharacter(62);
// $result = $myscraper->getPeople(1123);
// $result = $myscraper->getCharacterStaff('manga',1);
// $result = $myscraper->getStat('manga',1);
$result = $myscraper->getPicture('anime',1);

print_r(memory_get_usage()."\n");
print_r($result);