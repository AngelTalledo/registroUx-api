<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Container\ContainerInterface;

return function (ContainerInterface $container) {
    $settings = $container->get('settings')['logger'];

    $logger = new Logger($settings['name']);
    $handler = new StreamHandler($settings['path'], $settings['level']);
    $logger->pushHandler($handler);

    return $logger;
};
