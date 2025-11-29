<?php
namespace App\controllers;

use App\models\AutoToken;
use App\models\Usuario;
use Exception;

class AutotokenController {
    
    /**
     * Realiza el login del usuario y genera un token de sesión
     */
    public function login($email, $password) {
        // Buscar usuario por email
        $usuario = Usuario::where('email', $email)->first();
        
        if (empty($usuario)) {
            throw new Exception("Usuario no encontrado", 404);
        }
        
        // Verificar contraseña
        // IMPORTANTE: En producción usar password_hash() y password_verify()
        if ($usuario->password !== $password) {
            throw new Exception("Credenciales inválidas", 401);
        }
        
        // Generar token aleatorio de 64 caracteres
        $token = bin2hex(random_bytes(32));
        
        // Eliminar tokens anteriores del usuario (sesiones previas)
        AutoToken::where('user_id', $usuario->id)->delete();
        
        // Crear nuevo token en la base de datos
        $authToken = new AutoToken();
        $authToken->user_id = $usuario->id;
        $authToken->token = $token;
        $authToken->save();
        
        // Retornar respuesta con token y datos del usuario
        return json_encode([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'role' => $usuario->role
            ]
        ]);
    }
    
    /**
     * Cierra la sesión eliminando el token
     */
    public function logout($token) {
        $deleted = AutoToken::where('token', $token)->delete();
        
        if ($deleted === 0) {
            throw new Exception("Token no encontrado", 404);
        }
        
        return json_encode([
            'success' => true,
            'message' => 'Sesión cerrada exitosamente'
        ]);
    }
    
    /**
     * Valida si un token es válido y retorna los datos del usuario
     */
    public function validarToken($token) {
        $authToken = AutoToken::where('token', $token)
            ->with('usuario')
            ->first();
        
        if (empty($authToken)) {
            throw new Exception("Token inválido o expirado", 401);
        }
        
        return json_encode([
            'valid' => true,
            'user' => [
                'id' => $authToken->usuario->id,
                'name' => $authToken->usuario->name,
                'email' => $authToken->usuario->email,
                'role' => $authToken->usuario->role
            ]
        ]);
    }
}