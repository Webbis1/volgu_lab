<?php

// src/Providers/ApiControllerProvider.php
namespace Providers;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class AthletesProvider implements ControllerProviderInterface
{
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory']
            ->assert('id', '\d+');
        $controller = $app['athletes.controller'];

        $controllers->get('/', [$controller, 'getAll']);
        $controllers->get('/{id}', [$controller, 'read']);
        $controllers->post('/', [$controller, 'create']);
        $controllers->put('/{id}', [$controller, 'update']);
        $controllers->delete('/{id}', [$controller, 'delete']);
        $controllers->get('/fields', [$controller, 'fields']);
        $controllers->get('/filter', function (Request $request) use ($controller) {
            $sportId = $request->query->get('sport_id') ?? null;
            $name = $request->query->get('name') ?? null;
            $birthday = $request->query->get('birthday') ?? null;

            return $controller->filter($sportId, $name, $birthday);
        });

        return $controllers;
    }
}
// athletes
// sports