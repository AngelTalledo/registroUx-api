<?php
require 'vendor/autoload.php';
$settings = require 'app/Config/settings.php';
$dbSettings = $settings['db'];
$pdo = new PDO("mysql:host={$dbSettings['host']};dbname={$dbSettings['database']}", $dbSettings['username'], $dbSettings['password']);
$stmt = $pdo->query("DESCRIBE teachers");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
