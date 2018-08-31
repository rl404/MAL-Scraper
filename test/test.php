<?php

require_once '../vendor/autoload.php'; // Autoload files using Composer autoload

use MalScraper\MalScraper;

echo MalScraper::getInfo('anime', 1);