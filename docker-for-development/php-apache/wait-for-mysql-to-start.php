#!/usr/local/bin/php
<?php
$dbDatabase = getenv("DB_DATABASE");
$dbHost = getenv("DB_HOST");
$dbPassword = getenv("DB_PASSWORD");
$dbUser = getenv("DB_USER");

$connectionAttempts = 0;
while (true) {
    $connectionAttempts++;
    try {
        new PDO("mysql:dbname=" . $dbDatabase . ";host=" . $dbHost, $dbUser, $dbPassword);
        echo "The MySQL server can be reached.\n";
        exit(0);
    }
    catch (PDOException $error) {
        if ($connectionAttempts > 30) {   
            echo "Failed to connect to the MySQL server. Giving up.\n";
            var_dump($error);
            exit(1);
        }
    }
    $delay = ceil($connectionAttempts * 0.5);
    echo "Failed to connect to the MySQL server. Retrying in " . $delay . " seconds. The server is probably still starting up.\n";
    sleep($delay);
}

?>
