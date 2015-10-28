## TWHL Beta Website

TWHL is a mapping and modding resource website for the Half-Life and Source engines.

- [Visit twhl.info](http://twhl.info)
- [Visit beta2.twhl.info](http://beta2.twhl.info)

### Getting a dev environment set up

TWHL4 uses the [Laravel](http://laravel.com/) framework. Detailed instructions can be found
in the Laravel docs, but here's the basic steps:

1. Get an Apache environment with MySQL (5.5+) and PHP (5.6+)
   - The easiest way to do this is to download [XAMPP](https://www.apachefriends.org/index.html)
   for your platform and follow the install instructions.
   - Put the **php**/**php.exe** executable path into your system's environment variables
   - Make sure you're using the right php version, OSX and some linux distros will ship
   with a different version.
2. Install [Node JS](https://nodejs.org/) for your platform
3. Install [Git](https://git-scm.com/) for your platform
   - If you're on Windows or OSX and aren't used to command line Git, you can install
   [SourceTree](https://www.sourcetreeapp.com/) which is pretty handy.
4. Install [Composer](https://getcomposer.org/) for your platform
5. Clone the twhl repo using Git
   - Make sure the repo folder has the correct read & execute permissions
6. In the repo's root folder, install the app dependencies using Composer by running:
   `composer install`
   - If you're on OSX, make sure your homebrew is up to date, and `brew install openssl`
   before doing this. Also open `php.ini` and add this line:
   `openssl.cafile=/usr/local/etc/openssl/cert.pem`
7. Add this to your `apache/conf/httpd.conf` - this config assumes your repo is
   at `C:\twhl`, change the path for your system as required.

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
8. Start up XAMPP and run Apache and MySQL (or MariaDB, whatever), and then get into
   PhpMyAdmin. Create a database called `twhl` with the collation `utf8mb4_unicode_ci`.
9. We're almost there. Find the `.env.example` file in the root folder, and copy it to
   a new file called simply `.env`. The default settings should be fine, but you can
   change them if you have a different setup for your DB server and so on.
10. At this point, [http://localhost:82/](http://localhost:82/) should give you the
   Laravel splash screen. If it doesn't, something's gone wrong. Otherwise, carry on...
11. In the git repo root folder, run:
   - `php artisan migrate --seed` (requires php on path)
      - I got an error doing this and had to run `mysql/bin/mysql_upgrade.exe`, but
	    this may be because I had an older version of MySQL installed. If you get an error
		about "Cannot load from mysql.proc", try doing this. Drop and re-create the twhl
		database before trying again.
   - `npm install --global gulp` (requires node.js on path)
   - `npm install`
   - `gulp`
12. Hopefully, you're done! Go to [http://localhost:82/auth/login](http://localhost:82/auth/login)
   to log in. User: `admin@twhl.info` // Pass: `admin`.

### Working with Laravel

Some general notes if you're not used to Laravel/Composer/Gulp:

- If `composer.json` changes, run `composer update` to get the latest library versions.
  Run it sometimes even if `composer.json` doesn't change. `composer dump-autoload` can
  sometimes be useful if the dependencies aren't updating correctly.
- Run `composer self-update` if it nags you, it's a good idea to stay up to date.
- When making changes to `*.less` and `*.js` files, you need to run `gulp` to bundle
  those changes into the compiled CSS and JS files. The easiest way to do this is to run
  `gulp watch`, which will watch the files for changes and auto-build when needed.