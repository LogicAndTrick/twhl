## TWHL Website

TWHL is a mapping and modding resource website for Half-Life 1 and related games.

- [Visit twhl.info](http://twhl.info)

### Getting a dev environment set up

TWHL4 uses the [Laravel](http://laravel.com/) framework. Detailed instructions can be found
in the Laravel docs, but here's the basic steps:

1. If you know how to use Docker, see the [docker-for-development](docker-for-development/readme.md) folder and skip many of the following steps.
2. Get an Apache environment with MySQL/MariaDB (5.5+) and PHP (8.4+)
   - The easiest way to do this is to download [WampServer](https://wampserver.aviatechno.net/?lang=en)
   for your platform and follow the install instructions.
   - Put the **php**/**php.exe** executable path into your system's environment variables
   - Make sure you're using the right php version, OSX and some linux distros will ship
   with a different version.
3. Install [Node JS](https://nodejs.org/) for your platform
4. Install [Git](https://git-scm.com/) for your platform
   - If you're on Windows or OSX and aren't used to command line Git, you can install
   [SourceTree](https://www.sourcetreeapp.com/) which is pretty handy. There's other options too such as GitKraken, GitHub Desktop, etc.
5. Install [Composer](https://getcomposer.org/) for your platform
6. Clone the twhl repo using Git
   - Make sure the repo folder has the correct read & execute permissions
7. In the repo's root folder, install the app dependencies using Composer by running:
   `composer install`
8. Add a virtual host to your Apache config, these instructions assume your repo is
   at `C:\twhl`, change the path for your system as required.
   - If you're using WampServer:
     - Go to http://localhost and click 'Add a virtual host'
     - Name = twhl
     - Path = C:/twhl/public
     - Select YES for PHP in FCGI mode and select PHP 8.4 as the version
       - You may need to follow the instructions on the page to set up FCGI mode
     - Click the 'Start the creation of the virtual host' button
     - This will create a host on your local machine at http://twhl/
   - If you want to use a different host name or port, or if you're using a different
       Apache setup, you can manually edit the `httpd.conf` file for your server. Here's an
       example that will host the site at http://localhost:82/:
       ```
       Listen 82
    
       <VirtualHost *:82>
           ServerName localhost
           DocumentRoot C:/twhl/public
           <Directory C:/twhl/public>
               Options Indexes FollowSymLinks Includes ExecCGI
               AllowOverride All
               Require all granted
           </Directory>
       </VirtualHost>
       ```
9. Start up your web server and run Apache and MySQL (or MariaDB, whatever), and then get into
   PhpMyAdmin. Create a database called `twhl` with the collation `utf8mb4_unicode_ci`.
10. We're almost there. Find the `.env.example` file in the root folder, and copy it to
    a new file called simply `.env`. The default settings should be fine, but you can
    change them if you have a different setup for your DB server and so on.
     - After creating the .env file, you should run `php artisan key:generate` in order to
       create an encryption key.
11. At this point, [http://twhl/](http://twhl/) should give you the
   Laravel splash screen. If it doesn't, something's gone wrong. Otherwise, carry on...
12. In the git repo root folder, run:
    - `php artisan migrate --seed`
    - `npm install`
    - `npm run development`
13. Hopefully, you're done! Go to [http://twhl/auth/login](http://twhl/auth/login)
   to log in. User: `admin@twhl.info` // Pass: `admin`.

### Working with Laravel

Some general notes if you're not used to Laravel/Composer:

- If `composer.json` (or `composer.lock`) changes, run `composer install` to get the latest library versions.
- Run `composer self-update` if it nags you, it's a good idea to stay up to date.
- When making changes to `*.css`, `*.scss` and `*.js` files, you need to run `npm run development` to bundle
  those changes into the compiled CSS and JS files. The easiest way to do this is to run
  `npm run watch`, which will watch the files for changes and auto-build when needed.