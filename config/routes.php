<?php

declare(strict_types=1);

return [
    ['GET', '/', 'DashboardController', 'index', true],
    ['GET', '/login', 'AuthController', 'login', false],
    ['POST', '/login', 'AuthController', 'authenticate', false],
    ['GET', '/register', 'AuthController', 'register', false],
    ['POST', '/register', 'AuthController', 'store', false],
    ['POST', '/logout', 'AuthController', 'logout', true],
    ['GET', '/password', 'AuthController', 'password', true],
    ['POST', '/password', 'AuthController', 'updatePassword', true],

    ['GET', '/relatives', 'RelativeController', 'index', true],
    ['GET', '/relatives/create', 'RelativeController', 'create', true],
    ['POST', '/relatives', 'RelativeController', 'store', true],
    ['GET', '/relatives/{id}/edit', 'RelativeController', 'edit', true],
    ['POST', '/relatives/{id}', 'RelativeController', 'update', true],
    ['POST', '/relatives/{id}/delete', 'RelativeController', 'destroy', true],

    ['GET', '/banquets', 'BanquetController', 'index', true],
    ['GET', '/banquets/create', 'BanquetController', 'create', true],
    ['POST', '/banquets', 'BanquetController', 'store', true],
    ['GET', '/banquets/{id}/edit', 'BanquetController', 'edit', true],
    ['POST', '/banquets/{id}', 'BanquetController', 'update', true],
    ['POST', '/banquets/{id}/delete', 'BanquetController', 'destroy', true],

    ['GET', '/gifts', 'GiftRecordController', 'index', true],
    ['GET', '/gifts/create', 'GiftRecordController', 'create', true],
    ['POST', '/gifts', 'GiftRecordController', 'store', true],
    ['GET', '/gifts/{id}/edit', 'GiftRecordController', 'edit', true],
    ['POST', '/gifts/{id}', 'GiftRecordController', 'update', true],
    ['POST', '/gifts/{id}/delete', 'GiftRecordController', 'destroy', true],

    ['GET', '/statistics', 'StatisticsController', 'index', true],
];
