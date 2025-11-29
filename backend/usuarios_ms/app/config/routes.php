<?php

use App\repositories\UsuariosRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    $app->get('/', function (Request $request, Response $response, $args) {
        $response->getBody()->write("Hello world!");
        return $response;
    });

    //esto se genera en base a la documentacion de Slim, creo que esto es para crud
    $app->group('/usuarios', function (RouteCollectorProxy $group) {
        $group->get('/all',  [UsuariosRepository::class, 'queryAllUsuarios']);
        $group->post('/login', [UsuariosRepository::class, 'login']);

        
    });

}

?>