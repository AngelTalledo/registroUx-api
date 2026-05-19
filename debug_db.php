<?php
require_once __DIR__ . '/vendor/autoload.php';
$settings = require __DIR__ . '/app/Config/settings.php';
$containerBuilder = new \DI\ContainerBuilder();
$settings($containerBuilder);
$container = $containerBuilder->build();

// Eloquent is initialized in the app flow, but we can do it here manually or just use PDO from settings
$dbSettings = $container->get('settings')['db'];
$dsn = "mysql:host={$dbSettings['host']};dbname={$dbSettings['database']}";
$pdo = new PDO($dsn, $dbSettings['username'], $dbSettings['password']);

echo "--- TABLE STRUCTURE ---\n";
$stmt = $pdo->query("DESCRIBE diagnostic_evaluations");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

echo "\n--- INDEXES ---\n";
$stmt = $pdo->query("SHOW INDEX FROM diagnostic_evaluations");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
