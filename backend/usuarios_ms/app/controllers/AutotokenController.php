<?php
namespace App\controllers;

use App\models\User;
use App\models\AutoToken;
use App\models\Usuario;
use Exception;

class AutoTokenController {
    
    public function login($username, $password) {
        $user = Usuario::where('userName', $username)->first();
        
        if (empty($user)) {
            throw new Exception("Usuario no encontrado", 404);
        }
        
        // Verificar contraseña (en producción usar password_verify)
        if ($user->password !== $password) {
            throw new Exception("Credenciales inválidas", 401);
        }
        
        // Generar token aleatorio
        $token = bin2hex(random_bytes(32));
        
        // Eliminar tokens anteriores del usuario
        AutoToken::where('userId', $user->id)->delete();
        
        // Crear nuevo token
        $authToken = new AutoToken();
        $authToken->userId = $user->id;
        $authToken->token = $token;
        $authToken->expiresAt = date('Y-m-d H:i:s', strtotime('+24 hours'));
        $authToken->save();
        
        return json_encode([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'userName' => $user->userName,
                'fullName' => $user->fullName,
                'email' => $user->email,
                'role' => $user->role
            ]
        ]);
    }
    
    public function logout($token) {
        $deleted = AutoToken::where('token', $token)->delete();
        
        if ($deleted === 0) {
            throw new Exception("Token no encontrado", 404);
        }
        
        return json_encode(['message' => 'Sesión cerrada exitosamente']);
    }
    
    public function validateToken($token) {
        $authToken = AutoToken::where('token', $token)
            ->where('expiresAt', '>', date('Y-m-d H:i:s'))
            ->with('user')
            ->first();
        
        if (empty($authToken)) {
            throw new Exception("Token inválido o expirado", 401);
        }
        
        return json_encode([
            'valid' => true,
            'user' => [
                'id' => $authToken->user->id,
                'userName' => $authToken->user->userName,
                'fullName' => $authToken->user->fullName,
                'email' => $authToken->user->email,
                'role' => $authToken->user->role
            ]
        ]);
    }
}