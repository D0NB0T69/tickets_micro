<?php
namespace App\models;

use Illuminate\Database\Eloquent\Model;
    
class Usuario extends Model{
    protected $table = "usuarios";
    //para fecha de creacion , pero lo dejamos en false
    public $timestamps = false;
}

