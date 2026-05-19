<?php
require "vendor/autoload.php";
$files = glob("app/Services/Implements/*.php");
foreach ($files as $file) {
    try {
        require_once $file;
        echo $file . " - OK\n";
    } catch (\Throwable $e) {
        echo $file . " - ERROR: " . $e->getMessage() . "\n";
    }
}

