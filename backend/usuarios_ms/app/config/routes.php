<?php

use App\repositories\UsuariosRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    
    // Ruta de prueba
    $app->get('/', function (Request $request, Response $response, $args) {
        $response->getBody()->write(json_encode([
            'message' => 'API de Usuarios - Sistema de Tickets',
            'version' => '1.0'
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Grupo de rutas para usuarios
    $app->group('/usuarios', function (RouteCollectorProxy $group) {
        
        // Obtener todos los usuarios
        $group->get('/all', [UsuariosRepository::class, 'queryAllUsuarios']);
        
        // Login
        $group->post('/login', [UsuariosRepository::class, 'login']);
        
        // Logout
        $group->post('/logout', [UsuariosRepository::class, 'logout']);
        
        // Validar token
        $group->post('/validar-token', [UsuariosRepository::class, 'validarToken']);
    });
};

?>