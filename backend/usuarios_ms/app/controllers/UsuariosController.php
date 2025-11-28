<?php
namespace App\controllers;
use Exception;

use App\models\Usuario;

class UsuariosController{ 
    public function getContactos(){
        //dentro de esta funcion basicamente se hacen las consultas
        //si quiero hacer una consulta especifica debo cambiar
        //lo que va despues de ::
        $rows = Usuario :: all();
        //A row se le agrega ->tojson, pors los cambios hechos en el UsuariosRepository
        //agregamos tambien una validacion 
        if (count($rows)==0){
            return null;
        }
        return $rows->toJson(); 
    }

    public function login($username, $password)
    {
        $row = Usuario::where('userName', $username)
            ->where('password', $password)
            ->first();
        if (empty($row)) {
            throw new Exception("User null", 1);
        }
        return $row->toJson();
    }
}