<?php

declare(strict_types=1);

require_once 'vendor/autoload.php';

$config = require_once 'config.php';

// PSR-17 implementation. Needs for the creating \Alserom\Viber\Psr17 instance and others.
$psr17Factory = new \Nyholm\Psr7\Factory\Psr17Factory();

// PSR-18 implementation.
// This object will set as one of the parameters which need to create \Alserom\Viber\Api instance.
$httpClient = new \Buzz\Client\Curl($psr17Factory);

// This object will set as one of the parameters which need to create \Alserom\Viber\Api instance.
$psr17 = \Alserom\Viber\Psr17::useForAll($psr17Factory);
