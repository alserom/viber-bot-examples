# Viber bot examples

This repository contains quick examples of how to build [Viber bots](https://developers.viber.com/docs/general/get-started/#get-started-with-bots) with usage [alserom/viber-php](https://github.com/alserom/viber-php) library.

> Do not recommend to use these examples in production, as it not the best code which you can write.

## Getting Started

These instructions will get you a copy of the project up and running on your local machine.

### Prerequisites

1. You should install [Docker Compose](https://docs.docker.com/compose/install/) **OR** be ready to configure web-server, PHP (>=7.1) and work with [composer](https://getcomposer.org/).
2. You must have an active Viber account on a platform which supports Public Accounts / bots.
3. You must have an active [bot account](https://partners.viber.com).

### Usage

* [Download](https://github.com/alserom/viber-bot-examples/archive/master.zip) or clone the repository.
```bash
git clone https://github.com/alserom/viber-bot-examples
cd viber-bot-examples
```

##### With Docker Compose

* Copy `.env.dist` to `.env` file.
```bash
cp .env.dist .env
```
* Edit `.env` file, placing your account authentication token to `API_TOKEN` and one of the available bots to `BOT_EXAMPLE`. See [Available bots](#available-bots) section.
* Start up an example with Docker Compose.
```bash
sudo docker-compose up --build
```

##### With a manually configured environment

* Configure your web-server that document root will be `<your-path-here>/viber-bot-examples/src/public` path.
* Edit `src/config.php` file, placing your account authentication token to `API_TOKEN` and webhook URL to `WEBHOOK_URL`.
> `WEBHOOK_URL` must be URL with valid and official SSL certificate from a trusted CA. [Ngrok](https://ngrok.com) can help with it.
* Edit `src/public/index.php` file, manually included PHP file with one of the available bots. See [Available bots](#available-bots) section.
* Install project dependencies with composer
```bash
cd src
composer install
```
* Set up webhook for the bot, running `setup.php` script. This can be done with a composer script.
```bash
composer setup
```

### What next?

Next, you can analyze `.php` files in the `src` directory for a better understanding of how to build bots.
* **base.php** - Base things, which need to creating `$api` and `$bot` instances.
* **setup.php** - Example of how to set webhook.
* **bots/\*/bot.php** - Main bot logic.
* **public/index.php** - Example of how to handling Viber callbacks (server requests) and return a response.

## Available bots

| Name      | Path                         | Description |
| --------- |:----------------------------:| ----------- |
| annoying  | `src/bots/annoying/bot.php`  | Simple echo bot |
| weather   | `src/bots/weather/bot.php`   | Demonstrates working with keyboards |
| isitup    | `src/bots/isitup/bot.php`    | Similar functionality as in the official [example](https://github.com/Viber/sample-bot-isitup) from Viber |

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
