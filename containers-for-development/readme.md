# Containerized local development

These instructions assume you're on Linux, MacOS, Windows with WSL, or another Unix-like system that can run .sh scripts.

## Starting a local development instance of TWHL

1. Install one of Docker Desktop, Podman Desktop, Docker Engine (with the Compose plugin), or Podman (with a Compose backend).
2. Create a [GitHub Personal Access Token](https://docs.github.com/en/authentication/keeping-your-account-and-data-secure/managing-your-personal-access-tokens#creating-a-fine-grained-personal-access-token) with the `read:packages` scope and add it to the file `~/.npmrc` like this:
   ```
   //npm.pkg.github.com/:_authToken=ghp_1234example567
   ```
   This step is necessary for the successful installation of the `@logicandtrick/twhl-wikicode-parser` NPM package.
3. Run `./up.sh` to build the images and start the containers. Your local development instance of TWHL can now be reached at http://localhost:1082 .
4. After making changes to `*.css`, `*.scss` and `*.js` files, you need to run `./npm.sh run prod` to bundle those changes into the compiled CSS and JS files.

## Updating NPM dependencies

1. Make your changes to package.json
2. Run `./npm.sh install`

## Updating Composer PHP dependencies

1. Make your changes to composer.json
2. Run `./composer.sh install`

## Running database migrations

1. Create a new migration in the `database/migrations` folder
2. Run `./artisan.sh migrate`

## Making changes to one of the views

1. Make your changes to a .blade.php file or a Blade template pattern
2. To test the change you need to clear the cache by running `./artisan.sh view:clear`

## Accessing the database

The MySQL database is accessible at `mysql://root:cs_canbunk2@localhost:3340/twhl`.

## Running Bash inside the PHP & Apache container for executing commands

Run `./compose.sh exec php-apache bash`

## Inspecting the Apache logs

Run `./apache-logs.sh`
