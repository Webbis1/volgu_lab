<?php
require_once __DIR__ . '/vendor/autoload.php';

use Providers\SportsProvider;
use Providers\AthletesProvider;
use Controllers\SportsController;
use Controllers\AthletesController;

$db_config = require_once __DIR__ . '/config.php';
$db = $db_config['db'];

use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();
$app['debug'] = true;


// index.php
$app['db'] = function () use ($db) {
    $host = $db['host'];
    $user = $db['user'];
    $pass = $db['password'];
    $db_name = $db['dbname'];
    return new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
};

$app['sports.controller'] = function ($app) {
    return new SportsController($app['db']);
};
$app['athletes.controller'] = function ($app) {
    return new AthletesController($app['db']);
};

// Разрешаем переопределение метода (PUT/DELETE)
$app->before(function (Request $request) {
    if ($request->headers->get('X-HTTP-Method-Override')) {
        $request->setMethod($request->headers->get('X-HTTP-Method-Override'));
    }
});

$app->mount(prefix: '/api/sports', controllers: new SportsProvider());
$app->mount(prefix: '/api/athletes', controllers: new AthletesProvider());

$app->run();
