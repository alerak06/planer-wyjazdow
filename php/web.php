<?php

use Triptrav\Controllers\HomeController;
use Triptrav\Controllers\UserController;
use Triptrav\Controllers\AuthController;
use Triptrav\Controllers\TripController;
use Triptrav\Controllers\AvailabilityController;
use Triptrav\Controllers\TransportController;

$router->get(
    '/',
    [
        HomeController::class,
        'index'
    ]
);

$router->get(
    '/login',
    [
        AuthController::class,
        'loginForm'
    ]
);


$router->post(
    '/login',
    [
        AuthController::class,
        'login'
    ]
);

$router->get(
    '/register',
    [
        AuthController::class,
        'register'
    ]
);


$router->post(
    '/register',
    [
        AuthController::class,
        'register'
    ]
);


$router->get(
    '/logout',
    [
        AuthController::class,
        'logout'
    ]
);

$router->get(
    '/profile',
    [
        UserController::class,
        'profile'
    ]
);

$router->get(
    '/trips',
    [
        TripController::class,
        'index'
    ]
);

$router->get(
    '/trips/create',
    [
        TripController::class,
        'createForm'
    ]
);

$router->post(
    '/trips/create',
    [
        TripController::class,
        'create'
    ]
);

$router->get(
    '/trip/{share_code}',
    [
        TripController::class,
        'show'
    ]
);

$router->post(
    '/trip/{share_code}/join',
    [
        TripController::class,
        'join'
    ]
);

$router->post(
    '/trip/{share_code}/availability',
    [
        AvailabilityController::class,
        'save'
    ]
);

$router->post(
    '/trip/{share_code}/toggle',
    [
        AvailabilityController::class,
        'toggle'
    ]
);

$router->post(
    '/trip/{share_code}/set-date',
    [
        TripController::class,
        'setDate'
    ]
);

$router->get(
    '/trip/{share_code}/transport/create',
    [
        TransportController::class,
        'create'
    ]
);


$router->post(
    '/trip/{share_code}/transport/create',
    [
        TransportController::class,
        'store'
    ]
);

$router->post(
    '/trip/{share_code}/transport',
    [
        TransportController::class,
        'store'
    ]
);

$router->post(
    '/trip/{shareCode}/transport/join',
    [
        TransportController::class,
        'joinCar'
    ]
);

$router->post(
    '/trip/{shareCode}/transport/leave',
    [TransportController::class, 'leaveCar']
);