<?php

// src/Providers/ApiControllerProvider.php
namespace Providers;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class SportsProvider implements ControllerProviderInterface
{
    // src/Providers/ApiControllerProvider.php
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory']
            ->assert('id', '\d+');
        $controller = $app['sports.controller'];

        $controllers->get('/', [$controller, 'getAll']);
        $controllers->get('/{id}', [$controller, 'read']);
        $controllers->post('/', [$controller, 'create']);
        $controllers->put('/{id}', [$controller, 'update']);
        $controllers->delete('/{id}', [$controller, 'delete']);
        $controllers->get('/fields', [$controller, 'fields']);

        return $controllers;
    }
}
// athletes
// sports