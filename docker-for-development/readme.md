Delete your `.env` file if you have one and run this in Bash or PowerShell to build the Docker images and start the containers:
```
docker compose up
```
The web server can then be reached at http://localhost:82 .

To install the Composer dependencies and create the `.env` file (if it does not exist), run:
```
docker compose exec php-apache twhl-install
```

To run `npm install` inside the Node container:
```
docker compose run node npm install
```

To start a Bash instance inside the PHP & Apache container for executing commands, run this:
```
docker compose exec php-apache bash
```

To see the Apache log, run this:
```
docker compose logs php-apache
```

To rebuild the images:
```
docker compose build
```
