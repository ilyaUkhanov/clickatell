# Mise en place du projet
- cp .env .env.local
- Dans .env.local, mettre env=prod et fournir les clefs API clickatell
- docker compose build --no-cache
- SERVER_NAME=clickatell.ilya-ukhanov.fr \
  APP_SECRET=123456 \
  CADDY_MERCURE_JWT_SECRET=123456 \
  docker compose -f compose.yaml -f compose.prod.yaml up -d --wait
- docker compose exec php apt update
- docker compose exec php apt install nodejs npm -y
- docker compose exec php npm install --force
- docker compose exec php npm run build
- docker compose exec php doctrine:migrations:diff
- docker compose exec php doctrine:migrations:migrate

Commande pour tester l'envoi des Campagnes publicitaires :
- docker compose exec php bin/console app:advance

S'il y a eu des changements dans le code, clear le cache et rebuild webpack :
- docker compose exec php bin/console c:c
- docker compose exec php npm run build

## Getting Started

1. If not already done, [install Docker Compose](https://docs.docker.com/compose/install/) (v2.10+)
2. Run `docker compose build --no-cache` to build fresh images
3. Run `docker compose up --pull always -d --wait` to set up and start a fresh Symfony project
4. Open `https://localhost` in your favorite web browser and [accept the auto-generated TLS certificate](https://stackoverflow.com/a/15076602/1352334)
5. Run `docker compose down --remove-orphans` to stop the Docker containers.

## Features

* Production, development and CI ready
* Just 1 service by default
* Blazing-fast performance thanks to [the worker mode of FrankenPHP](https://github.com/dunglas/frankenphp/blob/main/docs/worker.md) (automatically enabled in prod mode)
* [Installation of extra Docker Compose services](docs/extra-services.md) with Symfony Flex
* Automatic HTTPS (in dev and prod)
* HTTP/3 and [Early Hints](https://symfony.com/blog/new-in-symfony-6-3-early-hints) support
* Real-time messaging thanks to a built-in [Mercure hub](https://symfony.com/doc/current/mercure.html)
* [Vulcain](https://vulcain.rocks) support
* Native [XDebug](docs/xdebug.md) integration
* Super-readable configuration

**Enjoy!**

## Docs

1. [Options available](docs/options.md)
2. [Using Symfony Docker with an existing project](docs/existing-project.md)
3. [Support for extra services](docs/extra-services.md)
4. [Deploying in production](docs/production.md)
5. [Debugging with Xdebug](docs/xdebug.md)
6. [TLS Certificates](docs/tls.md)
7. [Using MySQL instead of PostgreSQL](docs/mysql.md)
8. [Using Alpine Linux instead of Debian](docs/alpine.md)
9. [Using a Makefile](docs/makefile.md)
10. [Updating the template](docs/updating.md)
11. [Troubleshooting](docs/troubleshooting.md)

## License

Symfony Docker is available under the MIT License.

## Credits

Created by [Kévin Dunglas](https://dunglas.dev), co-maintained by [Maxime Helias](https://twitter.com/maxhelias) and sponsored by [Les-Tilleuls.coop](https://les-tilleuls.coop).
