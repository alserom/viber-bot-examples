<?php

declare(strict_types=1);

use Zend\HttpHandlerRunner\Emitter\SapiEmitter;
use Zend\HttpHandlerRunner\RequestHandlerRunner;
use Nyholm\Psr7Server\ServerRequestCreator;

require_once __DIR__ . '/../base.php';



/*
 * Use one of the available bots. Available bots placed in src/bots directory.
 * If you do not use docker, include bot manually.
 *      $botPath = __DIR__ . '/../bots/annoying/bot.php';
 */
$botPath = sprintf('%s/../bots/%s/bot.php', __DIR__, getenv('BOT_EXAMPLE'));
if (!file_exists($botPath)) {
    $botPath = __DIR__ . '/../bots/annoying/bot.php';
}
$bot = require $botPath;



/*
 * We need an instance of an object which implements Psr\Http\Message\ServerRequestInterface to pass it to our bot.
 * Nyholm\Psr7Server\ServerRequestCreator can help with it
 */
$serverRequestCreator = new ServerRequestCreator(
    $psr17Factory, // ServerRequestFactory
    $psr17Factory, // UriFactory
    $psr17Factory, // UploadedFileFactory
    $psr17Factory  // StreamFactory
);

/*
 * We need to process a server request and return response.
 * As Alserom\Viber\Bot implements Psr\Http\Server\RequestHandlerInterface we can use
 *      zendframework/zend-httphandlerrunner package for doing this.
 *
 * For the alternative, you can receive a ready response from the bot and doing with it what you want.
 *
 *      $serverRequest = $serverRequestCreator->fromGlobals();
 *      $response = $bot->handle($serverRequest);
 *
 * Next, you can emit this response with some emitter.
 *
 *      $emitter = new Zend\HttpHandlerRunner\Emitter\SapiEmitter();
 *      $emitter->emit($response);
 *
 * Or writing your own logic for emitting a response with PSR-7.
 * See example how this can be doing https://stackoverflow.com/a/48717426
 */
$runner = new RequestHandlerRunner(
    $bot,
    new SapiEmitter(),
    [$serverRequestCreator, 'fromGlobals'],
    function (Throwable $e) use ($psr17Factory) {
        return $psr17Factory
            ->createResponse(500)
            ->withBody($psr17Factory->createStream($e->getMessage()));
    }
);

$runner->run();
