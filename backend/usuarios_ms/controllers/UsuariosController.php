<?php
namespace App\controllers;

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
}