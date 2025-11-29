<?php

namespace App\repositories;

//mismos uses de routes.php 

use App\models\Usuario;
use App\controllers\UsuariosController;
use App\controllers\AutotokenController;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class UsuariosRepository {
    
    private static $codesError = [
        404 => 404, // Not Found
        401 => 401, // Unauthorized
        'default' => 400 // Bad Request
    ];

    /**
     * Obtener todos los usuarios
     */
    public static function queryAllUsuarios(Request $request, Response $response) {
        $controller = new UsuariosController();
        $data = $controller->getContactos();
        
        if (empty($data)) {
            return $response->withStatus(204); // No Content
        }
        
        $response->getBody()->write($data);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Login de usuario
     */
    public static function login(Request $request, Response $response) {
        try {
            // Obtener datos del body
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            
            // Validar que existan los campos necesarios
            if (!isset($data['email']) || !isset($data['password'])) {
                throw new Exception("Email y password son requeridos", 400);
            }
            
            // Realizar login
            $controller = new AutotokenController();
            $resultado = $controller->login($data['email'], $data['password']);
            
            // Responder con el token
            $response->getBody()->write($resultado);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
                
        } catch (Exception $ex) {
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
    
    /**
     * Logout de usuario
     */
    public static function logout(Request $request, Response $response) {
        try {
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            
            if (!isset($data['token'])) {
                throw new Exception("Token es requerido", 400);
            }
            
            $controller = new AutotokenController();
            $resultado = $controller->logout($data['token']);
            
            $response->getBody()->write($resultado);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
                
        } catch (Exception $ex) {
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
    
    /**
     * Validar token
     */
    public static function validarToken(Request $request, Response $response) {
        try {
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            
            if (!isset($data['token'])) {
                throw new Exception("Token es requerido", 400);
            }
            
            $controller = new AutotokenController();
            $resultado = $controller->validarToken($data['token']);
            
            $response->getBody()->write($resultado);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
                
        } catch (Exception $ex) {
            $status = self::$codesError[$ex->getCode()] ?? self::$codesError['default'];
            
            $error = json_encode([
                'valid' => false,
                'message' => $ex->getMessage()
            ]);
            
            $response->getBody()->write($error);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus($status);
        }
    }
}