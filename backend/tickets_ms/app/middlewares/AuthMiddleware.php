<?php
namespace App\middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class AuthMiddleware {
    
    /**
     * Validar token con el microservicio de usuarios
     */
    public function __invoke(Request $request, RequestHandler $handler): Response {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (empty($authHeader)) {
            return $this->unauthorized('Token no proporcionado');
        }
        
        // Extraer el token
        $token = str_replace('Bearer ', '', $authHeader);
        
        // Validar token con el MS de Usuarios
        try {
            $ch = curl_init('http://127.0.0.1:8000/usuarios/validar-token');
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode(['token' => $token]),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT => 5
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                return $this->unauthorized('Token inválido o expirado');
            }
            
            $userData = json_decode($response, true);
            
            if (!$userData || !isset($userData['valid']) || !$userData['valid']) {
                return $this->unauthorized('Token inválido');
            }
            
            // Agregar datos del usuario al request
            $request = $request->withAttribute('user', $userData['user']);
            
        } catch (\Exception $e) {
            return $this->unauthorized('Error al validar token: ' . $e->getMessage());
        }
        
        return $handler->handle($request);
    }
    
    /**
     * Respuesta de no autorizado
     */
    private function unauthorized($message): Response {
        $response = new \Slim\Psr7\Response();
        $response->getBody()->write(json_encode([
            'success' => false,
            'message' => $message
        ]));
        
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(401);
    }
}