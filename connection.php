<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'u344222945_madina');
define('DB_PASSWORD', 'cN#93$NM4u68');
define('DB_NAME', 'u344222945_madinaDb');

$mysqli = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);

// Check connection
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>