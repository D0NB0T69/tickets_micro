<?php
namespace App\models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model {
    protected $table = "tickets";
    
    public $timestamps = true;
    
    protected $fillable = [
        'titulo',
        'descripcion',
        'estado',
        'gestor_id',
        'admin_id'
    ];
    
    // Relación: ticket pertenece a un gestor
    public function gestor() {
        return $this->belongsTo(Usuario::class, 'gestor_id');
    }
    
    // Relación: ticket pertenece a un admin
    public function admin() {
        return $this->belongsTo(Usuario::class, 'admin_id');
    }
    
    // Relación: ticket tiene múltiples actividades
    public function actividades() {
        return $this->hasMany(TicketActividad::class, 'ticket_id');
    }
}