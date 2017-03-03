<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require("client.php");

connect();
$auth = checkAuth();

header('Access-Control-Allow-Origin: *');

if (isset($_GET["force"])) {
    forceAuth();
    exit("Successfully authent");
}

if (isset($_GET["who-am-i"])) {
    exit(getUser());
}

if (!$auth) {
    sendError("You are not connected", 1);
}

if (!isset($_GET["service"])) {
    sendError("No service defined", 2);
}

$service = $_GET["service"];
$callback = isset($_GET["callback"]) ? $_GET["callback"] : null;
$ticket = $_COOKIE["PHPSESSID"];
$user = getUser();

if (!isAllowed($service, $user)) {
    sendError("The specified service is not allowed by the user", 3);
}

$body = askService($service, $ticket, $user);

if ($callback) {
    echo $callback . "('" . $body . "');";
} else {
    echo $body;
}
