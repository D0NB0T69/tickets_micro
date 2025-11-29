<?php
namespace App\models;

use Illuminate\Database\Eloquent\Model;

class AutoToken extends Model {
    protected $table = "auth_tokens";
    
    // Habilitar timestamps automáticos
    public $timestamps = true;
    
    protected $fillable = [
        'user_id', 
        'token'
    ];

    // Relación: un token pertenece a un usuario
    public function usuario() {
        return $this->belongsTo(Usuario::class, 'user_id');
    }
}