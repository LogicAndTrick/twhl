# Dockerized local development

## Starting a local development instance of TWHL
1. Create a [GitHub Personal Access Token](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens#creating-a-fine-grained-personal-access-token) with the `read:packages` scope and add it to the file `~/.npmrc` like this:
	```
	//npm.pkg.github.com/:_authToken=ghp_1234example567
	```
	This step is necessary for the successful installation of the `@logicandtrick/twhl-wikicode-parser` NPM package.
2. Run `docker compose up` to build the Docker images and start the containers. Your local development instance of TWHL can now be reached at http://localhost:82 .
3. When making changes to `*.css`, `*.scss` and `*.js` files, you need to run `docker compose run --rm node npm run development` to bundle those changes into the compiled CSS and JS files. The easiest way to do this is to run `docker compose run --rm node npm run watch`, which will watch the files for changes and auto-build when needed.

## Updating NPM dependencies
1. Make your changes to package.json
2. Run `docker compose run --rm node npm install`

## Updating Composer PHP dependencies
1. Make your changes to composer.json
2. Run `docker compose run --rm composer composer install`

## Running Bash inside the PHP & Apache container for executing commands
Run `docker compose exec php-apache bash`

## Inspecting the Apache logs
Run `docker compose logs php-apache`
