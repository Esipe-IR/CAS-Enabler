<?php

require("vendor/autoload.php");
require("config.php");

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

function connect() {
    $cas_host = "cas.u-pem.fr";
    $cas_port = 443;
    $cas_context = "";

    phpCAS::setDebug();
    phpCAS::client(CAS_VERSION_2_0, $cas_host, $cas_port, $cas_context);
    phpCAS::setNoCasServerValidation();
}

function checkAuth() {
    return phpCAS::checkAuthentication();
}

function forceAuth() {
    return phpCAS::forceAuthentication();
}

function getUser() {
    return phpCAS::getUser();
}

function sendError($msg, $code) {
    $response = array(
        "status" => false,
        "message" => $msg,
        "code" => $code
    );

    if (isset($_GET["callback"])) {
        echo $_GET["callback"] . "('" . json_encode($response) . "');"; 
    } else {
        echo json_encode($response);
    }

    exit();
}

function isAllowed($service, $user) {
    $db = getDB();

    $q = $db->prepare("SELECT * FROM service_user AS su WHERE su.service LIKE ? AND su.user LIKE ?");
    $q->execute(array(
        "%" . $service . "%",
        "%" . $user . "%"
    ));

    $result = $q->fetchAll();
    
    if (count($result) < 1) {
        return false;
    }

    return true;
}

function allow_service($service, $user) {
    $db = getDB();

    try {
        $q = $db->prepare('INSERT INTO service_user ("service", "user") VALUES (?, ?)');
        $q->execute(array(
            $service,
            $user
        ));
    } catch (Exception $e) {
    }
}

function askService($service, $ticket, $user) {
    $client = new Client();

    try {
        $url = $service . "?ticket=" . $ticket . "&user=" . $user;
        $request = new Request("GET", $url);
        $response = $client->send($request);
    } catch (Exception $e) {
        return "{}";
    }

    return (string) $response->getBody();
}
