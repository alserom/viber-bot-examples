<?php

declare(strict_types=1);

require_once __DIR__ . '/../../base.php';

use Alserom\Viber\Api;
use Alserom\Viber\Bot;
use Alserom\Viber\Event\Type\Message as MessageEvent;
use Alserom\Viber\Event\Type\Subscribed;
use Alserom\Viber\Message;
use Alserom\Viber\Entity\User;
use Alserom\Viber\Entity\Keyboard;
use Alserom\Viber\Entity\Button;
use Alserom\Viber\Collection\ButtonCollection;
use Monolog\Logger;
use Monolog\Handler\ErrorLogHandler;
use Alserom\Viber\Entity\Message\Text;
use Alserom\Viber\Entity\Sender;

/*
 * Creating an API instance.
 * $config, $psr17 and $httpClient was provided by line "require_once __DIR__ . '/../../base.php';"
 */
$api = new Api($config['API_TOKEN'], $psr17, $httpClient);

// Creating sender
$sender = new Sender();
$sender
    ->setName('Is It Up')
    ->setAvatar('https://raw.githubusercontent.com/devrelv/drop/master/151-icon.png');

// Welcome message generator
$welcomeMessage = function (User $user, bool $isSubscribed) use ($sender): ?Message {

    // If the user already subscribed we don't need to show a welcome message
    if ($isSubscribed) {
        return null;
    }

    /*
     * We want that user subscribes to bot manually with clicking a subscribe button.
     * This will be triggering 'subscribed' event. See https://developers.viber.com/docs/api/rest-bot-api/#subscribed
     * So we creating keyboard message with hiding the input field and with one button which does not do anything.
     */
    $message = new Message('keyboard');
    $message
        ->setMinApiVersion(4) // For properly work  ActionType='none' and InputFieldState='hidden'
        ->setSender($sender);

    $keyboard = new Keyboard();
    $keyboard->setInputFieldState('hidden');

    $button = new Button();
    $button
        ->setText('Subscribe to get more info')
        ->setActionType('none');

    // Setting keyboard buttons
    $keyboard->setButtons(new ButtonCollection($button));

    // Adding keyboard to message
    $message->setKeyboard($keyboard);

    return $message;
};

// Subscribed handler
$subscribedHandler = function (Subscribed $event, Api $api) use ($sender) {
    $user = $event->getUser();

    $message = new Message('text');
    $message
        ->setTo($user)
        ->setSender($sender)
        ->setText(sprintf(
            'Hi there %s. I am %s! Feel free to ask me if a web site is down for everyone or just you. '
            . 'Just send me a name of a website and I\'ll do the rest!',
            $user->getName(),
            $sender->getName()
        ));

    $api->sendMessage($message);
};

// Message handler
$messageHandler = function (MessageEvent $event, Api $api) use ($sender, $httpClient) {
    $msgEntity = $event->getMessage();
    $message = new Message();

    // Setting up sender and recipient
    $message
        ->setSender($sender)
        ->setTo($event->getUser());

    if (!$msgEntity instanceof Text) {
        $message->setText('Sorry. I can only understand text messages.');
        $api->sendMessage($message);
        return;
    }

    $message->setText('One second...Let me check!');
    $api->sendMessage($message);

    $urlToCheck = $msgEntity->getText();

    try {
        $url = 'https://api.downfor.cloud/httpcheck/' . $urlToCheck;
        $req = $api->getPsr17()->getRequestFactory()->createRequest('GET', $url);
        $res = $httpClient->sendRequest($req);

        if ($res->getStatusCode() !== 200) {
            $text = 'Something is wrong with isup.me.';
        } else {
            $body = $res->getBody()->__toString();
            $data = json_decode($body, true);
            if (!isset($data['isDown'])) {
                $text = 'Snap...Something is wrong with isup.me.';
            } elseif ($data['isDown']) {
                $text = 'Oh no! "' . $urlToCheck . '" is broken.';
            } else {
                $text = 'Hooray! "' . $urlToCheck . '" looks good to me.';
            }
        }
    } catch (\Exception $ex) {
        $text = 'Something is wrong with isup.me.';
    }

    $message->setText($text);
    $api->sendMessage($message);
};

// Creating logger
$log = new Logger('isitup');
$log->pushHandler(new ErrorLogHandler());

// Setting up a welcome message and logger in bot options.
$botOptions = [
    'welcome_message' => $welcomeMessage,
    'logger'          => $log,
    'debug'           => true
];

// Creating a bot instance.
$bot = new Bot($api, $botOptions);

// Registering handler for the subscribed event.
$bot->onSubscribed($subscribedHandler);

// Registering handler for the message event.
$bot->onMessage($messageHandler);


return $bot;
