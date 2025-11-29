<?php
namespace App\repositories;

use App\controllers\TicketsController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class TicketsRepository {
    
    private static $codesError = [
        404 => 404,
        401 => 401,
        403 => 403,
        400 => 400,
        'default' => 500
    ];
    
    /**
     * Obtener usuario autenticado del request
     */
    private static function getUsuarioAuth(Request $request) {
        $user = $request->getAttribute('user');
        
        if (empty($user)) {
            throw new Exception("No autorizado", 401);
        }
        
        return $user;
    }
    
    /**
     * Listar todos los tickets
     */
    public static function listar(Request $request, Response $response) {
        try {
            $controller = new TicketsController();
            
            // Obtener parámetros de consulta
            $params = $request->getQueryParams();
            $filtros = [];
            
            if (isset($params['estado'])) {
                $filtros['estado'] = $params['estado'];
            }
            
            if (isset($params['gestor_id'])) {
                $filtros['gestor_id'] = $params['gestor_id'];
            }
            
            if (isset($params['admin_id'])) {
                $filtros['admin_id'] = $params['admin_id'];
            }
            
            $data = $controller->getTodos($filtros);
            
            if (empty($data)) {
                $response->getBody()->write(json_encode([]));
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/json');
            }
            
            $response->getBody()->write($data);
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $ex) {
            return self::errorResponse($response, $ex);
        }
    }
    
    /**
     * Obtener ticket por ID
     */
    public static function obtener(Request $request, Response $response, $args) {
        try {
            $controller = new TicketsController();
            $ticketId = $args['id'];
            
            $data = $controller->getPorId($ticketId);
            
            $response->getBody()->write($data);
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $ex) {
            return self::errorResponse($response, $ex);
        }
    }
    
    /**
     * Crear nuevo ticket
     */
    public static function crear(Request $request, Response $response) {
        try {
            // Obtener usuario autenticado
            $user = self::getUsuarioAuth($request);
            
            // Solo gestores pueden crear tickets
            if ($user['role'] !== 'gestor') {
                throw new Exception("Solo los gestores pueden crear tickets", 403);
            }
            
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            
            if (!isset($data['titulo']) || !isset($data['descripcion'])) {
                throw new Exception("Título y descripción son requeridos", 400);
            }
            
            $controller = new TicketsController();
            $resultado = $controller->crear($data, $user['id']);
            
            $response->getBody()->write($resultado);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(201);
                
        } catch (Exception $ex) {
            return self::errorResponse($response, $ex);
        }
    }
    
    /**
     * Actualizar estado del ticket
     */
    public static function actualizarEstado(Request $request, Response $response, $args) {
        try {
            $user = self::getUsuarioAuth($request);
            $ticketId = $args['id'];
            
            // Solo admins pueden cambiar el estado
            if ($user['role'] !== 'admin') {
                throw new Exception("Solo los admins pueden cambiar el estado", 403);
            }
            
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            
            if (!isset($data['estado'])) {
                throw new Exception("Estado es requerido", 400);
            }
            
            // Validar estado
            $estadosValidos = ['abierto', 'en_progreso', 'resuelto', 'cerrado'];
            if (!in_array($data['estado'], $estadosValidos)) {
                throw new Exception("Estado no válido", 400);
            }
            
            $controller = new TicketsController();
            $resultado = $controller->actualizarEstado($ticketId, $data['estado'], $user['id']);
            
            $response->getBody()->write($resultado);
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $ex) {
            return self::errorResponse($response, $ex);
        }
    }
    
    /**
     * Asignar ticket a admin
     */
    public static function asignar(Request $request, Response $response, $args) {
        try {
            $user = self::getUsuarioAuth($request);
            $ticketId = $args['id'];
            
            // Solo admins pueden asignar tickets
            if ($user['role'] !== 'admin') {
                throw new Exception("Solo los admins pueden asignar tickets", 403);
            }
            
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            
            if (!isset($data['admin_id'])) {
                throw new Exception("admin_id es requerido", 400);
            }
            
            $controller = new TicketsController();
            $resultado = $controller->asignar($ticketId, $data['admin_id'], $user['id']);
            
            $response->getBody()->write($resultado);
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $ex) {
            return self::errorResponse($response, $ex);
        }
    }
    
    /**
     * Agregar comentario
     */
    public static function agregarComentario(Request $request, Response $response, $args) {
        try {
            $user = self::getUsuarioAuth($request);
            $ticketId = $args['id'];
            
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            
            if (!isset($data['mensaje']) || empty(trim($data['mensaje']))) {
                throw new Exception("Mensaje es requerido", 400);
            }
            
            $controller = new TicketsController();
            $resultado = $controller->agregarComentario($ticketId, $user['id'], $data['mensaje']);
            
            $response->getBody()->write($resultado);
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $ex) {
            return self::errorResponse($response, $ex);
        }
    }
    
    /**
     * Obtener historial del ticket
     */
    public static function historial(Request $request, Response $response, $args) {
        try {
            $ticketId = $args['id'];
            
            $controller = new TicketsController();
            $data = $controller->getHistorial($ticketId);
            
            if (empty($data)) {
                $response->getBody()->write(json_encode([]));
                return $response->withStatus(200)
                    ->withHeader('Content-Type', 'application/json');
            }
            
            $response->getBody()->write($data);
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $ex) {
            return self::errorResponse($response, $ex);
        }
    }
    
    /**
     * Generar respuesta de error
     */
    private static function errorResponse(Response $response, Exception $ex) {
        $status = self::$codesError[$ex->getCode()] ?? self::$codesError['default'];
        
        $error = json_encode([
            'success' => false,
            'message' => $ex->getMessage()
        ]);
        
        $response->getBody()->write($error);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}