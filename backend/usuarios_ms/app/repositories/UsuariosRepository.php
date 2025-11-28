<?php

namespace App\repositories;

//mismos uses de routes.php 

use App\controllers\UsuariosController;
use App\models\Usuario;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class UsuariosRepository
{
    public static function queryAllUsuarios(Request $request, Response $response)
    {
        $controller = new UsuariosController();
        $data = $controller->getContactos();
        if (empty($data)) {
            //basicamente si no hay nada en la consulta enviamos un error atravez de un Response
            //el error 204 es que no encuentra nada 
            return $response->withStatus(204);
        }
        //me va escribir la consulta
        $response->getBody()->write($data);
        return $response->withHeader('Content-Type', 'application/json');
    }

    private static $codesError = [
        1 => 401,
        'default' => 400
    ];

     public function login(Request $request, Response $response)
    {
        try {
            $body = $request->getBody()->getContents();
            $data = json_decode($body, true);
            $controller = new UsuariosController();
            $user = $controller->login($data['user'], $data['pwd']);
            $response
                ->withHeader('Content-Type', 'application/json')
                ->getBody()
                ->write($user);
            return $response;
        } catch (Exception $ex) {
            $status =  $this->codesError[$ex->getCode()] ?? $this->codesError['default'];
            return $response->withStatus($status);
        }
    }

}
