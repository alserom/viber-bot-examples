<?php

declare(strict_types=1);

if (getenv('WH_FROM_NGROK') === false) {
    die;
}

require_once 'base.php';

use Alserom\Viber\Api;
use Alserom\Viber\Entity\Webhook;

$whUrl = $config['WEBHOOK_URL'] ?? null;

if ($whUrl === null) {
    $req = $psr17->getRequestFactory()->createRequest('GET', 'http://ngrok:4040/api/tunnels/command_line');
    try {
        $res = $httpClient->sendRequest($req);
        $data = json_decode($res->getBody()->__toString(), true);
        $whUrl = $data['public_url'] ?? null;
    } catch (\Psr\Http\Client\ClientExceptionInterface $ex) {
        echo 'Can not get url from ngrok: ' . $ex->getMessage();
        echo PHP_EOL;
    }
}

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
