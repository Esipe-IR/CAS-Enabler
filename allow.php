<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require("client.php");

connect();
$auth = checkAuth();

if (!$auth) {
    sendError("You are not connected", 1);
}

if (!isset($_GET["service"])) {
    sendError("No service defined", 2);
}

$user = getUser();
$service = $_GET["service"];

if (isset($_POST["allow"])) {
    allow_service($service, $user);
    exit("Successfully allowed");
}

?>

<html>
<head>
    <title>Allow App</title>
</head>
<body>
    <p>
        Please be carefull, you will allow an app to connect yourself with your UPEM account.
        Only allow app that are linked to UPEM.
    </p>

    <form method="post">
        <input type="submit" name="allow" value="Allow <?php echo $service ?>"/>
    </form>
</body>
</html>
