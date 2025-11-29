<?php
namespace App\models;

use Illuminate\Database\Eloquent\Model;
    
class Usuario extends Model{
    protected $table = "users";
    //para fecha de creacion , pero lo dejamos en false
    public $timestamps = false;
    //para la contraseña creo 
    protected $hidden = ['password'];
}

