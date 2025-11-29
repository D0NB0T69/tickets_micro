<?php
namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Usuario extends Model {
    protected $table = "users";
    
    public $timestamps = true;
    
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];
    
    protected $hidden = ['password'];
    
    // Relación: usuario tiene múltiples tickets como gestor
    public function ticketsComoGestor() {
        return $this->hasMany(Ticket::class, 'gestor_id');
    }
    
    // Relación: usuario tiene múltiples tickets como admin
    public function ticketsComoAdmin() {
        return $this->hasMany(Ticket::class, 'admin_id');
    }
}