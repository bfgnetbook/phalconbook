<?php

use Phalcon\Mvc\Router;
// Creamos un objeto router
$router = new Router();
$router->setDefaultNamespace('Holidays\Controllers');

$router->add(
    '/',
    [
        'controller' => 'index',
        'action'     => 'index',
    ]
)->via(['POST', 'GET']);;

$router->add(
    '/login',
    [
        'controller' => 'index',
        'action'     => 'login',
    ]
)->via(['POST', 'GET']);

$router->add(
    '/private',
    [
        'controller' => 'private',
        'action'     => 'index',
    ]
);

$router->add(
    '/private/manipulation',
    [
        'controller' => 'private',
        'action'     => 'manipulation',
    ]
)->via(['POST', 'GET']);

$router->add(
    '/private/manipulation/{id}',
    [
        'controller' => 'private',
        'action'     => 'manipulation',
    ]
)->via(['POST', 'GET']);

$router->add(
    '/private/delete/{id}',
    [
        'controller' => 'private',
        'action'     => 'delete',
    ]
)->via(['POST', 'GET']);

$router->add(
    '/private/apply/{id}',
    [
        'controller' => 'private',
        'action'     => 'apply',
    ]
)->via(['POST', 'GET']);

$router->add(
    '/private/logout',
    [
        'controller' => 'private',
        'action'     => 'logout',
    ]
);

return $router;
