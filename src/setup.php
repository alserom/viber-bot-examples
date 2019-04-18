<?php

declare(strict_types=1);

require_once 'base.php';

use Alserom\Viber\Api;
use Alserom\Viber\Entity\Webhook;

$whUrl = $config['WEBHOOK_URL'] ?? null;

if ($whUrl === null) {
    throw new Exception('Webhook URL is missing!');
}

// Initializing API
$api = new Api($config['API_TOKEN'], $psr17, $httpClient);

// Setting webhook
$webhook = new Webhook($whUrl);
$webhook
    ->setSendName(true)
    ->setSendPhoto(true);

// You can use Webhook::setEventTypes for setting which events would get a callback for.
// But remember, that 'message', 'subscribed' and 'unsubscribed' is mandatory events and can not be filtered.
// $webhook->setEventTypes(['seen', 'failed']);

try {
    $api->setWebhook($webhook);
    echo sprintf('Webhook %s was set successfully' . PHP_EOL, $whUrl);
} catch (\Exception $ex) {
    echo sprintf('Webhook not set! Error: %s', $ex->getMessage());
}
