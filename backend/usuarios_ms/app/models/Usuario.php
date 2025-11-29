<?php
namespace App\models;

use Illuminate\Database\Eloquent\Model;

    
class Usuario extends Model {
    protected $table = "users";
    
    // Habilitar timestamps automáticos de Laravel
    public $timestamps = true;
    
    // Campos que se pueden asignar masivamente
    protected $fillable = [
        'name',
        'email', 
        'password',
        'role'
    ];
    
    // Ocultar campos sensibles en JSON
    protected $hidden = ['password'];
    
    // Relación: un usuario puede tener múltiples tokens
    public function tokens() {
        return $this->hasMany(AutoToken::class, 'user_id');
    }
    
    // Relación: tickets creados por el gestor
    public function ticketsCreados() {
        return $this->hasMany('App\modelos\Ticket', 'gestor_id');
    }
    
    // Relación: tickets asignados al admin
    public function ticketsAsignados() {
        return $this->hasMany('App\modelos\Ticket', 'admin_id');
    }
}

