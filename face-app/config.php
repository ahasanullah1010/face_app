<?php

$mysqli = new mysqli("localhost", "root", "", "faceapp");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}


?>