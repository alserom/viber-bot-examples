<?php

declare(strict_types=1);

require_once __DIR__ . '/../../base.php';

use Alserom\Viber\Api;
use Alserom\Viber\Bot;
use Alserom\Viber\Event\Type\Message as MessageEvent;
use Alserom\Viber\Message;
use Alserom\Viber\Entity\User;

/*
 * Creating an API instance.
 * $config, $psr17 and $httpClient was provided by line "require_once __DIR__ . '/../../base.php';"
 */
$api = new Api($config['API_TOKEN'], $psr17, $httpClient);

// Setting up a welcome message in bot options.
$botOptions = [
    'welcome_message' => function (User $user) {
        $message = new Message();
        $message->setText(sprintf(
            "Hello, %s!\nI am an annoying bot (mischievous)\n"
              . 'Just send me a message and you understand what I doing (shrug)',
            $user->getName()
        ));

        return $message;
    }
];

// Creating a bot instance.
$bot = new Bot($api, $botOptions);

// Registering handler for the message event.
$bot->onMessage(function (MessageEvent $event, Api $api) {
    $message = new Message();
    $message->setEntity($event->getMessage());
    $message->setTo($event->getUser());

    $api->sendMessage($message);
});


return $bot;
