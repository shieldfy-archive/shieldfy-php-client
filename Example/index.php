<?php

require_once('../vendor/autoload.php');

$shieldfy = \Shieldfy\Guard::init([
    'app_key'       => getenv('SHIELDFY_APP_KEY'),
    'app_secret'    => getenv('SHIELDFY_APP_SECRET'),
]);

$db = new PDO('sqlite::memory:');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$shieldfy->attachPDO($db);

echo '<h1> Hi I am an Example </h1>';
