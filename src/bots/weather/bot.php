<?php

declare(strict_types=1);

require_once __DIR__ . '/../../base.php';

use Alserom\Viber\Api;
use Alserom\Viber\Bot;
use Alserom\Viber\Event\Type\Message as MessageEvent;
use Alserom\Viber\Message;
use Alserom\Viber\Entity\User;
use Alserom\Viber\Entity\Keyboard;
use Alserom\Viber\Entity\Button;
use Alserom\Viber\Collection\ButtonCollection;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Alserom\Viber\Entity\Message as MsgEntity;

/*
 * Creating an API instance.
 * $config, $psr17 and $httpClient was provided by line "require_once __DIR__ . '/../../base.php';"
 */
$api = new Api($config['API_TOKEN'], $psr17, $httpClient);

// Keyboard generator
$generateKeyboard = function (User $user): Keyboard {
    // Creating keyboard
    $keyboard = new Keyboard();

    $button1 = new Button();
    $button1
        ->setText('By location')
        ->setActionType('location-picker')
        ->setActionBody('not-supported');

    $button2 = new Button();
    $button2
        ->setText(sprintf('In my country (%s)', $user->getCountry()))
        ->setActionType('reply')
        ->setActionBody('country');

    // Setting keyboard buttons
    $keyboard->setButtons(new ButtonCollection($button1, $button2));

    return $keyboard;
};

// Welcome message generator
$welcomeMessage = function (User $user) use ($generateKeyboard): Message {
    // Creating message
    $message = new Message();

    // Setting the message text
    $message->setText(sprintf(
        "Hello, %s!\nI am weather bot (sun)(cloud)(rain)\n"
        . 'I can check the weather by location or in your country (earth)',
        $user->getName()
    ));

    // Adding keyboard to message
    $message->setKeyboard($generateKeyboard($user));

    // As we use location-picker as ActionType on one of the buttons we must set min api version to 3 or higher.
    $message->setMinApiVersion(3);

    return $message;
};

// Message handler
$messageHandler = function (MessageEvent $event, Api $api) use ($generateKeyboard) {
    $msgEntity = $event->getMessage();
    $lazyMsg = 'LOL, my creator so lazy that not implement this feature. '
        . 'But I can give you a link where you can see what you want.';
    if ($msgEntity instanceof MsgEntity\Location) {
        $location = $msgEntity->getLocation();
        $message = new Message();
        if ($location === null) {
            $message->setText('Something went wrong (sick)');
        } else {
            $message->setText(sprintf(
                "%s\nhttps://weather.com/weather/today/l/%s,%s",
                $lazyMsg,
                $location->getLat(),
                $location->getLon()
            ));
        }
    } elseif ($msgEntity instanceof MsgEntity\Text) {
        $text = $msgEntity->getText();
        if ($text === 'not-supported') {
            $message = new Message();
            $message->setText('Hm, seems your Viber client doesn\'t support this feature (sad)');
        } elseif ($text === 'country') {
            $message = new Message();
            $message->setText(sprintf(
                "%s\nhttps://www.google.com/search?q=Show+me+weather+in+%s",
                $lazyMsg,
                $event->getUser()->getCountry()
            ));
        } else {
            $message = new Message();
            $message->setText('Sorry, I don\'t understand you (confused)');
        }
    } else {
        $message = new Message();
        $message->setText('Sorry, I don\'t understand this type of message (confused)');
    }

    // Setting recipient
    $message->setTo($event->getUser());

    // Adding keyboard to message
    $message->setKeyboard($generateKeyboard($event->getUser()));

    // As we use location-picker as ActionType on one of the buttons we must set min api version to 3 or higher.
    $message->setMinApiVersion(3);

    // Sending message
    $api->sendMessage($message);
};

// Creating logger
$log = new Logger('weather');
$log->pushHandler(new ErrorLogHandler());

// Setting up a welcome message and logger in bot options.
$botOptions = [
    'welcome_message' => $welcomeMessage,
    'logger'          => $log,
    'debug'           => true
];

// Creating a bot instance.
$bot = new Bot($api, $botOptions);

// Registering handler for the message event.
$bot->onMessage($messageHandler);


return $bot;
