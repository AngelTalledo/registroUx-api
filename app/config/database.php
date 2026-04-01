<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Container\ContainerInterface;

return function (ContainerInterface $container) {
    $settings = $container->get('settings')['db'];

    $capsule = new Capsule;
    $capsule->addConnection($settings);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();

    return $capsule;
};
