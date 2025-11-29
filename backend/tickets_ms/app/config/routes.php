<?php
use App\repositories\TicketsRepository;
use App\middlewares\AuthMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {
    
    // Ruta de prueba (sin autenticación)
    $app->get('/', function (Request $request, Response $response, $args) {
        $response->getBody()->write(json_encode([
            'message' => 'API de Tickets - Sistema de Soporte',
            'version' => '1.0',
            'endpoints' => [
                'GET /tickets' => 'Listar tickets',
                'GET /tickets/{id}' => 'Obtener ticket',
                'POST /tickets' => 'Crear ticket (gestor)',
                'PUT /tickets/{id}/estado' => 'Actualizar estado (admin)',
                'PUT /tickets/{id}/asignar' => 'Asignar ticket (admin)',
                'POST /tickets/{id}/comentarios' => 'Agregar comentario',
                'GET /tickets/{id}/historial' => 'Ver historial'
            ]
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Grupo de rutas para tickets (CON autenticación)
    $app->group('/tickets', function (RouteCollectorProxy $group) {
        
        // Listar todos los tickets
        $group->get('', [TicketsRepository::class, 'listar']);
        
        // Obtener ticket por ID
        $group->get('/{id}', [TicketsRepository::class, 'obtener']);
        
        // Crear nuevo ticket
        $group->post('', [TicketsRepository::class, 'crear']);
        
        // Actualizar estado del ticket
        $group->put('/{id}/estado', [TicketsRepository::class, 'actualizarEstado']);
        
        // Asignar ticket a admin
        $group->put('/{id}/asignar', [TicketsRepository::class, 'asignar']);
        
        // Agregar comentario
        $group->post('/{id}/comentarios', [TicketsRepository::class, 'agregarComentario']);
        
        // Obtener historial del ticket
        $group->get('/{id}/historial', [TicketsRepository::class, 'historial']);
        
    })->add(AuthMiddleware::class); // Middleware de autenticación
};